document.addEventListener('DOMContentLoaded', function() {
    // --- Data Retrieval ---
    const dataEl = document.getElementById('calendarData');
    if (!dataEl) return; // Guard clause in case this JS loads on other pages

    const isVerified = dataEl.getAttribute('data-verified') == '1';
    const hasActive = dataEl.getAttribute('data-has-active') == '1'; 
    const dailyCounts = JSON.parse(dataEl.getAttribute('data-daily-counts') || '{}'); 
    const takenSlots = JSON.parse(dataEl.getAttribute('data-taken-slots') || '{}');
    const calendarStatus = JSON.parse(dataEl.getAttribute('data-status') || '{}');

    // --- Shared Helpers ---
    function getLocalYMD(dateObj) {
        const year = dateObj.getFullYear();
        const month = String(dateObj.getMonth() + 1).padStart(2, '0');
        const day = String(dateObj.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }

    function checkBlockers(dateStr) {
        if (hasActive) {
            new bootstrap.Modal(document.getElementById('activeAppointmentModal')).show();
            return true;
        }
        if (!isVerified) {
            new bootstrap.Modal(document.getElementById('unverifiedModal')).show();
            return true;
        }
        const status = calendarStatus[dateStr];
        if (status === 'closed' || status === 'full') {
            return true; 
        }
        return false;
    }

    // --- 1. MOBILE LOGIC ---
    function initMobileView() {
        const stripContainer = document.getElementById('mobileDateStrip');
        const timeContainer = document.getElementById('mobileTimeGrid');
        if (!stripContainer) return; // Mobile view elements missing

        const today = new Date();
        const daysToRender = 30;
        const standardTimes = ['09:00 AM', '10:00 AM', '11:00 AM', '12:00 PM', '01:00 PM', '02:00 PM', '03:00 PM', '04:00 PM', '05:00 PM'];

        for (let i = 0; i < daysToRender; i++) {
            let d = new Date();
            d.setDate(today.getDate() + i);
            let dateStr = getLocalYMD(d);
            let dayName = d.toLocaleDateString('en-US', { weekday: 'short' });
            let dayNum = d.getDate();
            
            let status = calendarStatus[dateStr];
            let isClosed = status === 'closed';
            let isFull = status === 'full';
            let isToday = i === 0;

            let cardClass = 'date-card';
            if (isToday) cardClass += ' is-today';
            if (isClosed || isFull) cardClass += ' disabled';

            let card = document.createElement('div');
            card.className = cardClass;
            card.innerHTML = `
                <div class="day-name">${dayName}</div>
                <div class="day-num">${dayNum}</div>
                ${isClosed ? '<span class="status-dot closed"></span>' : ''}
                ${isFull ? '<span class="status-dot full"></span>' : ''}
            `;
            
            if (!isClosed && !isFull && !isToday) {
                card.onclick = () => selectMobileDate(d, dateStr, card);
            } else if (isToday) {
                card.onclick = () => openTodayModal(d, dateStr);
            }

            stripContainer.appendChild(card);
        }

        function selectMobileDate(dateObj, dateStr, cardElement) {
            if (checkBlockers(dateStr)) return;

            document.querySelectorAll('.date-card').forEach(c => c.classList.remove('active'));
            cardElement.classList.add('active');
            
            document.getElementById('mobileInitialPrompt').style.display = 'none';
            document.getElementById('mobileTimeSection').style.display = 'block';
            
            document.getElementById('mobileSelectedDateText').innerText = dateObj.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });

            timeContainer.innerHTML = '';
            let taken = takenSlots[dateStr] || [];
            let availableCount = 0;

            standardTimes.forEach(time => {
                let isTaken = taken.includes(time);
                let col = document.createElement('div');
                col.className = 'col-4 col-sm-3'; 
                
                if (isTaken) {
                    col.innerHTML = `<div class="time-slot disabled">${time.replace(' ', '')}</div>`;
                } else {
                    availableCount++;
                    let btn = document.createElement('div');
                    btn.className = 'time-slot available';
                    btn.innerHTML = `${time}`;
                    btn.onclick = () => openBookingModal(dateStr, dateObj, time, 'mobile');
                    col.appendChild(btn);
                }
                timeContainer.appendChild(col);
            });

            document.getElementById('mobileNoSlots').style.display = availableCount === 0 ? 'block' : 'none';
            if(availableCount === 0) timeContainer.innerHTML = ''; 
        }
    }

    // --- 2. DESKTOP LOGIC ---
    function initDesktopCalendar() {
        var calendarEl = document.getElementById('calendar');
        if (!calendarEl) return;

        let events = [];
        
        // Month View Counts
        for (const [date, count] of Object.entries(dailyCounts)) {
            let status = calendarStatus[date];
            if (status !== 'closed' && status !== 'full') {
                let color = count >= 3 ? '#D97706' : '#3B82F6'; 
                events.push({
                    title: count + ' Booked',
                    start: date,
                    allDay: true,
                    classNames: ['booking-badge'],
                    backgroundColor: color,
                    borderColor: 'transparent',
                    textColor: '#fff'
                });
            }
        }

        // Day View Gray Slots
        for (const [date, times] of Object.entries(takenSlots)) {
            times.forEach(timeStr => {
                let timeParts = timeStr.match(/(\d+):(\d+) (\w+)/);
                if(timeParts) {
                    let hours = parseInt(timeParts[1]);
                    let minutes = timeParts[2];
                    let amp = timeParts[3];
                    if (amp === "PM" && hours < 12) hours += 12;
                    if (amp === "AM" && hours === 12) hours = 0;
                    let isoStart = date + 'T' + hours.toString().padStart(2, '0') + ':' + minutes + ':00';
                    let isoEnd = date + 'T' + (hours + 1).toString().padStart(2, '0') + ':' + minutes + ':00';
                    
                    events.push({
                        title: 'Booked',
                        start: isoStart,
                        end: isoEnd,
                        backgroundColor: '#e2e8f0', 
                        borderColor: '#cbd5e1',
                        textColor: '#94a3b8',
                        classNames: ['booked-slot-event'],
                        display: 'background' 
                    });
                }
            });
        }

        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            themeSystem: 'standard',
            headerToolbar: { left: 'title', right: 'today prev,next' },
            events: events,
            validRange: { start: new Date(), end: new Date(new Date().setDate(new Date().getDate() + 31)) },
            
            // Time Grid Settings
            slotMinTime: '09:00:00', 
            slotMaxTime: '18:00:00', 
            slotDuration: '01:00:00', 
            allDaySlot: false,
            expandRows: true, 
            height: 'auto',   

            dayCellClassNames: function(arg) {
                let dateStr = getLocalYMD(arg.date);
                let status = calendarStatus[dateStr];
                if (status === 'closed') return ['day-closed'];
                if (status === 'full') return ['day-full'];
                return [];
            },

            dateClick: function(info) {
                let dateStr = info.dateStr;
                if(dateStr.includes('T')) dateStr = dateStr.split('T')[0];
                
                if (checkBlockers(dateStr)) return;
                
                let clickedDate = new Date(dateStr + 'T00:00:00');
                let today = new Date(); today.setHours(0,0,0,0);
                
                if (clickedDate.getTime() === today.getTime()) {
                    openTodayModal(clickedDate, dateStr);
                    return;
                }
                
                openBookingModal(dateStr, clickedDate, null, 'desktop');
            }
        });
        calendar.render();
        
        const dayBtn = document.getElementById('btnDayView');
        const monthBtn = document.getElementById('btnMonthView');

        if(dayBtn) {
            dayBtn.addEventListener('click', () => {
                calendar.changeView('timeGridDay');
                toggleViewBtns('day');
            });
        }
        if(monthBtn) {
            monthBtn.addEventListener('click', () => {
                calendar.changeView('dayGridMonth');
                toggleViewBtns('month');
            });
        }
    }

    function toggleViewBtns(active) {
        const dayBtn = document.getElementById('btnDayView');
        const monthBtn = document.getElementById('btnMonthView');
        if(active === 'day') {
            dayBtn.classList.replace('btn-outline-light', 'btn-light'); dayBtn.classList.add('text-primary');
            monthBtn.classList.replace('btn-light', 'btn-outline-light'); monthBtn.classList.remove('text-primary');
        } else {
            monthBtn.classList.replace('btn-outline-light', 'btn-light'); monthBtn.classList.add('text-primary');
            dayBtn.classList.replace('btn-light', 'btn-outline-light'); dayBtn.classList.remove('text-primary');
        }
    }

    function openTodayModal(dateObj, dateStr) {
        document.getElementById('todayDateDisplay').innerText = dateObj.toLocaleDateString(undefined, { 
            weekday: 'long', month: 'long', day: 'numeric' 
        });
        const listContainer = document.getElementById('todaySlotsList');
        const emptyMsg = document.getElementById('todayNoSlots');
        listContainer.innerHTML = ''; 
        
        let taken = takenSlots[dateStr] || [];
        
        if (taken.length === 0) {
            emptyMsg.style.display = 'block';
        } else {
            emptyMsg.style.display = 'none';
            taken.forEach(time => {
                let badge = document.createElement('span');
                badge.className = 'badge bg-secondary opacity-75 fs-6 fw-normal py-2 px-3 rounded-pill';
                badge.innerText = time;
                listContainer.appendChild(badge);
            });
        }
        new bootstrap.Modal(document.getElementById('todayModal')).show();
    }

    window.openBookingModal = function(dateStr, dateObj, preSelectedTime, mode) {
        document.getElementById('modalDateInput').value = dateStr;
        const monthNames = ["JAN", "FEB", "MAR", "APR", "MAY", "JUN", "JUL", "AUG", "SEP", "OCT", "NOV", "DEC"];
        document.getElementById('summaryMonth').innerText = monthNames[dateObj.getMonth()];
        document.getElementById('summaryDay').innerText = dateObj.getDate();

        const timeWrapper = document.getElementById('desktopTimeSelectWrapper');
        const timeInput = document.getElementById('modalTimeInput');
        const timeDisplay = document.getElementById('summaryTime');
        const desktopSelect = document.getElementById('desktopTimeSelect');

        if (mode === 'mobile') {
            timeWrapper.style.display = 'none'; 
            timeInput.value = preSelectedTime;
            timeDisplay.innerText = preSelectedTime;
            desktopSelect.removeAttribute('required'); 
        } else {
            timeWrapper.style.display = 'block';
            timeInput.value = ''; 
            desktopSelect.value = '';
            desktopSelect.setAttribute('required', 'required');
            timeDisplay.innerText = "Select in form below";
            
            let taken = takenSlots[dateStr] || [];
            let options = desktopSelect.options;
            let hasDisabled = false;
            for (let i = 0; i < options.length; i++) {
                if (options[i].value === "") continue;
                if (taken.includes(options[i].value)) {
                    options[i].disabled = true;
                    options[i].innerText = options[i].value + " (Booked)";
                    hasDisabled = true;
                } else {
                    options[i].disabled = false;
                    options[i].innerText = options[i].value;
                }
            }
            document.getElementById('timeSlotWarning').style.display = hasDisabled ? 'block' : 'none';
        }
        new bootstrap.Modal(document.getElementById('bookingModal')).show();
    };

    window.updateSummaryTime = function(val) {
        document.getElementById('summaryTime').innerText = val || "Select in form below";
    };

    initMobileView();
    initDesktopCalendar();
});