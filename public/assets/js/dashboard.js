/*
 * MATH-QN AI dashboard charts.
 * Replace the arrays below when connecting to your API.
 */
document.addEventListener('DOMContentLoaded', () => {
  Chart.defaults.font.family = 'Inter, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif';
  Chart.defaults.color = '#59627a';

  const knowledgeCtx = document.getElementById('knowledgeChart');
  if (knowledgeCtx) {
    const knowledgeData = window.dashboardKnowledgeChartData || {
      labels: ['Đại số', 'Hàm số', 'Hình học', 'Thống kê & Xác suất'],
      values: [7.1, 4.8, 6.5, 6.0],
      colors: ['#54bf92', '#efb229', '#f05b51', '#9443c7']
    };

    new Chart(knowledgeCtx, {
    type: 'doughnut',
    data: {
      labels: knowledgeData.labels,
      datasets: [{
        data: knowledgeData.values,
        backgroundColor: knowledgeData.colors,
        borderColor: '#ffffff',
        borderWidth: 2,
        hoverOffset: 3
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      cutout: '68%',
      plugins: { legend: { display: false }, tooltip: { enabled: true } }
    }
    });
  }

  const progressCtx = document.getElementById('progressChart');
  if (progressCtx) {
    const progressData = window.dashboardProgressChartData || {
      labels: ['Lần 1', 'Lần 2', 'Lần 3', 'Lần 4', 'Lần 5'],
      values: [5.0, 5.5, 6.0, 6.5, 6.2]
    };

    new Chart(progressCtx, {
    type: 'line',
    data: {
      labels: progressData.labels,
      datasets: [{
        label: 'Điểm số qua các lần thi thử',
        data: progressData.values,
        borderColor: '#1464f4',
        backgroundColor: '#1464f4',
        borderWidth: 3,
        pointRadius: 5,
        pointHoverRadius: 7,
        pointBackgroundColor: '#1464f4',
        pointBorderWidth: 0,
        tension: 0.25
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      interaction: { intersect: false, mode: 'index' },
      plugins: {
        legend: {
          position: 'bottom',
          labels: { usePointStyle: true, boxWidth: 8, padding: 18 }
        },
        tooltip: { callbacks: { label: ctx => ` ${ctx.parsed.y.toFixed(1)}` } }
      },
      scales: {
        y: {
          min: 0,
          max: 10,
          ticks: { stepSize: 2 },
          grid: { color: '#e9eef6' },
          border: { display: false }
        },
        x: {
          grid: { display: false },
          border: { display: false }
        }
      }
    }
    });
  }
});
