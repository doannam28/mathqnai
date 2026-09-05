/*
 * MATH-QN AI dashboard charts.
 * Replace the arrays below when connecting to your API.
 */
document.addEventListener('DOMContentLoaded', () => {
  Chart.defaults.font.family = 'Inter, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif';
  Chart.defaults.color = '#59627a';

  const knowledgeCtx = document.getElementById('knowledgeChart');
  new Chart(knowledgeCtx, {
    type: 'doughnut',
    data: {
      labels: ['Đại số', 'Hàm số', 'Hình học', 'Thống kê & Xác suất'],
      datasets: [{
        data: [7.1, 4.8, 6.5, 6.0],
        backgroundColor: ['#54bf92', '#efb229', '#f05b51', '#9443c7'],
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

  const progressCtx = document.getElementById('progressChart');
  new Chart(progressCtx, {
    type: 'line',
    data: {
      labels: ['Lần 1', 'Lần 2', 'Lần 3', 'Lần 4', 'Lần 5'],
      datasets: [{
        label: 'Điểm số qua các lần thi/thực hành',
        data: [5.0, 5.5, 6.0, 6.5, 6.2],
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
});
