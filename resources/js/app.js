
import Alpine from 'alpinejs';
import Chart from 'chart.js/auto';
import TomSelect from 'tom-select';

window.Alpine = Alpine;
window.Chart = Chart;
window.TomSelect = TomSelect;

Alpine.start();

function money(value) {
    return new Intl.NumberFormat('es-CL', {
        style: 'currency',
        currency: 'CLP',
        maximumFractionDigits: 0,
    }).format(value);
}

function renderDashboardCharts() {
    const chartData = document.getElementById('dashboard-chart-data');
    const flujoCanvas = document.getElementById('flujoMensualChart');
    const deudasCanvas = document.getElementById('deudasSociosChart');

    if (!chartData || !flujoCanvas || !deudasCanvas) {
        return;
    }

    const data = JSON.parse(chartData.textContent);

    Chart.getChart(flujoCanvas)?.destroy();

    new Chart(flujoCanvas, {
        type: 'bar',
        data: {
            labels: data.flujoMensual.map((item) => item.periodo),
            datasets: [
                {
                    label: 'Ingresos',
                    data: data.flujoMensual.map((item) => item.ingresos),
                    backgroundColor: '#059669',
                    borderRadius: 5,
                },
                {
                    label: 'Egresos',
                    data: data.flujoMensual.map((item) => item.egresos),
                    backgroundColor: '#e11d48',
                    borderRadius: 5,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                },
                tooltip: {
                    callbacks: {
                        label: (context) => `${context.dataset.label}: ${money(context.parsed.y)}`,
                    },
                },
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: (value) => money(value),
                    },
                },
            },
        },
    });

    if (data.deudasPorSocio.length === 0) {
        return;
    }

    Chart.getChart(deudasCanvas)?.destroy();

    new Chart(deudasCanvas, {
        type: 'doughnut',
        data: {
            labels: data.deudasPorSocio.map((item) => item.socio),
            datasets: [
                {
                    label: 'Deuda',
                    data: data.deudasPorSocio.map((item) => item.deuda),
                    backgroundColor: ['#d97706', '#2563eb', '#7c3aed', '#0891b2', '#db2777'],
                    borderColor: '#ffffff',
                    borderWidth: 2,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '58%',
            plugins: {
                legend: {
                    position: 'bottom',
                },
                tooltip: {
                    callbacks: {
                        label: (context) => `${context.label}: ${money(context.parsed)}`,
                    },
                },
            },
        },
    });
}

function initTomSelects() {
    document.querySelectorAll('select[data-tom-select]').forEach((select) => {
        if (select.tomselect) {
            return;
        }

        new TomSelect(select, {
            create: select.dataset.tomCreate === 'true',
            allowEmptyOption: true,
            persist: false,
            plugins: select.multiple ? ['remove_button'] : [],
            sortField: {
                field: 'text',
                direction: 'asc',
            },
        });
    });
}

function onlyMoneyDigits(value, allowNegative = false) {
    const rawValue = String(value ?? '').trim();
    const isNegative = allowNegative && rawValue.startsWith('-');
    const unsignedValue = rawValue.replace(/^-/, '');

    if (/^\d+([.,]\d{1,2})?$/.test(unsignedValue)) {
        const decimalValue = Number(unsignedValue.replace(',', '.'));

        if (Number.isFinite(decimalValue)) {
            return `${isNegative ? '-' : ''}${Math.trunc(decimalValue)}`;
        }
    }

    const digits = rawValue.replace(/\D/g, '');

    return `${isNegative ? '-' : ''}${digits}`;
}

function formatMoneyInputValue(value, allowNegative = false) {
    const normalized = onlyMoneyDigits(value, allowNegative);
    const isNegative = normalized.startsWith('-');
    const digits = normalized.replace(/\D/g, '');

    if (digits === '') {
        return '';
    }

    const formatted = new Intl.NumberFormat('es-CL', {
        maximumFractionDigits: 0,
    }).format(Number(digits));

    return `${isNegative ? '-' : ''}$ ${formatted}`;
}

function cleanMoneyInputValue(value, allowNegative = false) {
    const normalized = onlyMoneyDigits(value, allowNegative);

    if (normalized === '' || normalized === '-') {
        return '';
    }

    return normalized;
}

function initMoneyInputs() {
    document.querySelectorAll('input[data-money-input]').forEach((input) => {
        if (input.dataset.moneyReady === 'true') {
            return;
        }

        const allowNegative = input.dataset.moneyNegative === 'true';
        input.dataset.moneyReady = 'true';
        input.inputMode = 'numeric';
        input.autocomplete = 'off';
        input.value = formatMoneyInputValue(input.value, allowNegative);

        input.addEventListener('input', () => {
            input.value = formatMoneyInputValue(input.value, allowNegative);
            input.setSelectionRange(input.value.length, input.value.length);
        });

        input.form?.addEventListener('submit', () => {
            input.value = cleanMoneyInputValue(input.value, allowNegative);
        });
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        initTomSelects();
        initMoneyInputs();
        renderDashboardCharts();
    });
} else {
    initTomSelects();
    initMoneyInputs();
    renderDashboardCharts();
}
