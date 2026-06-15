/**
 * LaraSEOScan Dashboard JS
 * Handles charts and interactive elements for the SEO Dashboard.
 */

document.addEventListener('DOMContentLoaded', function () {
    // Initialize charts if elements exist
    initScoreGauge();
    initIssuesPie();
    initTrendChart();
});

/**
 * Initialize SEO Score Gauge Chart
 */
function initScoreGauge() {
    const chartDom = document.getElementById('seoScoreChart');
    if (!chartDom) return;

    const myChart = echarts.init(chartDom);
    const score = parseInt(chartDom.getAttribute('data-score')) || 0;

    let color = '#ef4444'; // red
    if (score >= 90) color = '#22c55e'; // green
    else if (score >= 70) color = '#eab308'; // yellow
    else if (score >= 50) color = '#f97316'; // orange

    const option = {
        series: [
            {
                type: 'gauge',
                startAngle: 180,
                endAngle: 0,
                min: 0,
                max: 100,
                splitNumber: 5, // 0, 20, 40, 60, 80, 100
                itemStyle: {
                    color: color,
                    shadowColor: 'rgba(0,138,255,0.45)',
                    shadowBlur: 10,
                    shadowOffsetX: 2,
                    shadowOffsetY: 2
                },
                progress: {
                    show: true,
                    roundCap: true,
                    width: 18
                },
                pointer: {
                    icon: 'path://M2090.36389,615.30999 L2090.36389,615.30999 C2091.48372,615.30999 2092.40383,616.194028 2092.44859,617.312956 L2096.90698,728.755929 C2097.05155,732.369577 2094.2393,735.416212 2090.62566,735.56078 C2090.53845,735.564269 2090.45117,735.566014 2090.36389,735.566014 L2090.36389,735.566014 C2086.74736,735.566014 2083.81557,732.63423 2083.81557,729.017692 C2083.81557,728.930412 2083.81732,728.84314 2083.82081,728.755929 L2088.2792,617.312956 C2088.32396,616.194028 2089.24407,615.30999 2090.36389,615.30999 Z',
                    length: '75%',
                    width: 16,
                    offsetCenter: [0, '5%']
                },
                axisLine: {
                    roundCap: true,
                    lineStyle: {
                        width: 14 // Thinner ring
                    }
                },
                axisTick: {
                    splitNumber: 2,
                    length: 6, // Shorter ticks
                    lineStyle: {
                        width: 1,
                        color: '#999'
                    }
                },
                splitLine: {
                    length: 10,
                    lineStyle: {
                        width: 2,
                        color: '#999'
                    }
                },
                axisLabel: {
                    distance: -40, // Move labels inside
                    color: '#6c757d',
                    fontSize: 10
                },
                title: {
                    show: false
                },
                detail: {
                    backgroundColor: '#fff',
                    borderColor: '#999',
                    borderWidth: 1,
                    width: '60%',
                    lineHeight: 30,
                    height: 30,
                    borderRadius: 8,
                    offsetCenter: [0, '35%'],
                    valueAnimation: true,
                    formatter: function (value) {
                        return '{value|' + value.toFixed(0) + '}{unit|/100}';
                    },
                    rich: {
                        value: {
                            fontSize: 28, // Smaller font
                            fontWeight: 'bolder',
                            color: '#777'
                        },
                        unit: {
                            fontSize: 12, // Smaller unit
                            color: '#999',
                            padding: [0, 0, -5, 5]
                        }
                    }
                },
                data: [
                    {
                        value: score
                    }
                ]
            }
        ]
    };

    myChart.setOption(option);

    window.addEventListener('resize', function () {
        myChart.resize();
    });
}

/**
 * Initialize Issues Distribution Pie Chart
 */
function initIssuesPie() {
    const chartDom = document.getElementById('issuesPieChart');
    if (!chartDom) return;

    const myChart = echarts.init(chartDom);

    // Get data attributes
    const critical = parseInt(chartDom.getAttribute('data-critical')) || 0;
    const error = parseInt(chartDom.getAttribute('data-error')) || 0;
    const warning = parseInt(chartDom.getAttribute('data-warning')) || 0;
    const info = parseInt(chartDom.getAttribute('data-info')) || 0;

    const option = {
        tooltip: {
            trigger: 'item'
        },
        legend: {
            top: '5%',
            left: 'center'
        },
        series: [
            {
                name: 'Issues',
                type: 'pie',
                radius: ['40%', '70%'],
                avoidLabelOverlap: false,
                itemStyle: {
                    borderRadius: 10,
                    borderColor: '#fff',
                    borderWidth: 2
                },
                label: {
                    show: false,
                    position: 'center'
                },
                emphasis: {
                    label: {
                        show: true,
                        fontSize: 20,
                        fontWeight: 'bold'
                    }
                },
                labelLine: {
                    show: false
                },
                data: [
                    { value: critical, name: 'Critical', itemStyle: { color: '#dc3545' } }, // Danger
                    { value: error, name: 'Error', itemStyle: { color: '#fd7e14' } }, // Orange
                    { value: warning, name: 'Warning', itemStyle: { color: '#ffc107' } }, // Warning
                    { value: info, name: 'Info', itemStyle: { color: '#0dcaf0' } } // Info
                ]
            }
        ]
    };

    myChart.setOption(option);

    window.addEventListener('resize', function () {
        myChart.resize();
    });
}

/**
 * Initialize Trend Chart (Placeholder for future history)
 */
function initTrendChart() {
    // Placeholder
}
