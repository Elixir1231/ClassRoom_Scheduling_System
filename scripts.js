document.addEventListener('DOMContentLoaded', () => {
    fetch('index.php') 
        .then(response => response.json())
        .then(data => {
            const tableBody = document.querySelector('#schedule tbody');
            const timeSlots = ["08:00-09:30", "10:00-11:30", "01:00-02:30", "03:00-04:30"];
            let scheduleHTML = '';

            timeSlots.forEach(timeSlot => {
                scheduleHTML += `<tr><td>${timeSlot}</td>`;
                for (let day = 0; day < 5; day++) {
                    const dayName = ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday"][day];
                    const entry = data.find(d => d.start_time <= timeSlot.split('-')[0] && d.end_time >= timeSlot.split('-')[1] && d.day_of_week === dayName);
                    scheduleHTML += `<td>${entry ? `${entry.room_name}: ${entry.subject_name} - ${entry.professor_name}` : 'None'}</td>`;
                }
                scheduleHTML += `</tr>`;
            });

            tableBody.innerHTML = scheduleHTML;

            // Highlight the current day
            const today = new Date().toLocaleString('en-US', { weekday: 'long' });
            document.querySelectorAll('th').forEach(th => {
                if (th.textContent === today) {
                    th.style.backgroundColor = '#f0f0f0'; 
                }
            });
        })
        .catch(error => console.error('Error fetching schedule:', error));
});
