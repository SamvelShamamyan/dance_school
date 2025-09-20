@extends('admin.layouts.main')
@section('content')

<style>
  :root{
    --time-col:80px;
    --slot-px:30px;

    /* динамические оффсеты, выставляются из JS */
    --navbar-h: 56px;   /* высота верхней навбарки */
    --head-h: 40px;     /* высота строки дней (будет измерена) */
  }
  @media (max-width: 576px){
    :root{ --time-col:64px; --slot-px:24px; }
  }

  * { box-sizing: border-box; }
  body { font-family: system-ui, -apple-system, Segoe UI, Roboto, Ubuntu, Arial; }

  /* ЕДИНЫЙ СКРОЛЛ-КОНТЕЙНЕР */
  .calendar-wrap {
    height: calc(100dvh - var(--navbar-h));
    overflow: auto; /* scroll здесь, не в .grid */
  }

  .calendar { display:grid; grid-template-rows:auto 1fr; min-width:0; }

  /* Шапка дней липкая ВНУТРИ .calendar-wrap */
  .day-head {
    display:grid;
    grid-template-columns: var(--time-col) repeat(7,1fr);
    border-bottom:1px solid #dee2e6;
    position:sticky; top:0; background:#fff; z-index:3;
    min-width:0;
  }
  .day-head > div { padding:.5rem; font-weight:600; border-left:1px solid #dee2e6; position:relative; }
  .day-head > div:first-child { border-left:none; }

  /* сетка больше НЕ скроллит по-своему */
  .grid { display:grid; grid-template-columns: var(--time-col) repeat(7,1fr); overflow:visible; position:relative; min-width:0; }
  .times { border-right:1px solid #dee2e6; }
  .time-cell { height: var(--slot-px); padding:2px 6px; color:#6c757d; font-size:.75rem; }

  .day {
    border-left:1px solid #dee2e6;
    background:repeating-linear-gradient(to bottom,#fff,#fff calc(var(--slot-px) - 1px),#f8f9fa var(--slot-px));
    position:relative;
    overflow:hidden;
    min-width:0;
  }
  .day-body { position:relative; height:100%; }
  .day-body:hover { background: repeating-linear-gradient(to bottom, #fff, #fff calc(var(--slot-px) - 1px), #eef2f7 var(--slot-px)); }

  .event {
    position:absolute;
    border-radius:.5rem;
    padding:.35rem .5rem;
    color:#fff;
    font-size:.82rem;
    overflow:hidden;
    box-shadow:0 4px 12px rgba(0,0,0,.08);
    cursor:pointer;
    white-space:nowrap; text-overflow:ellipsis;
  }
  .event.blue{background:#0d6efd;}
  .event.green{background:#198754;}
  .event.purple{background:#6f42c1;}
  .event.orange{background:#fd7e14;}

  .now-line { position:absolute; left:var(--time-col); right:0; height:0; border-top:2px solid #dc3545; z-index:2; }

  .grip {
    position:absolute; top:6px; bottom:6px; right:-4px; width:8px;
    cursor:col-resize; z-index:5;
  }
  .grip::after{ content:''; position:absolute; top:0; bottom:0; left:3px; width:2px; background:#adb5bd; border-radius:1px; }
  .grip:hover::after { background:#6c757d; }

  @media (max-width: 420px){ .event{ font-size:.75rem; } }

  /* === Mobile one-day mode === */
  .calendar.mobile { --mobile-gap: 0px; }
  .calendar.mobile .grid,
  .calendar.mobile .day-head { grid-template-columns: var(--time-col) 1fr !important; }
  .calendar.mobile .grid { overflow:visible; }
  .calendar.mobile .grid .day { display:none; }
  .calendar.mobile .grid .day.show { display:block; }

  /* В шапке показываем только «Время» и выбранный день */
  .calendar.mobile .day-head > div { display:none; }
  .calendar.mobile .day-head > div:first-child { display:block; } /* «Время» */
  .calendar.mobile.show-col-1 .day-head > div:nth-child(2),
  .calendar.mobile.show-col-2 .day-head > div:nth-child(3),
  .calendar.mobile.show-col-3 .day-head > div:nth-child(4),
  .calendar.mobile.show-col-4 .day-head > div:nth-child(5),
  .calendar.mobile.show-col-5 .day-head > div:nth-child(6),
  .calendar.mobile.show-col-6 .day-head > div:nth-child(7),
  .calendar.mobile.show-col-7 .day-head > div:nth-child(8) { display:block; }

  /* Полоса табов — sticky внутри .calendar-wrap, сразу под шапкой */
  .day-tabs {
    position: sticky; top: var(--head-h); z-index:3;
    background:#fff; border-bottom:1px solid #dee2e6;
    padding:.35rem .5rem; gap:.25rem; overflow:auto; white-space:nowrap;
    display:none;
  }
  .day-tabs .btn { padding:.25rem .5rem; border-radius:999px; }
  .day-tabs .btn.active { color:#fff; background:#0d6efd; border-color:#0d6efd; }
  .calendar.mobile + .day-tabs { display:flex; }

  /* компактнее шрифты и отступы на мобильных */
  @media (max-width: 576px){
    .event { font-size:.78rem; padding:.3rem .45rem; border-radius:.4rem; }
    .time-cell { font-size:.7rem; padding:2px 6px; }
    .navbar .btn, .navbar .custom-select { font-size:.85rem; }
    .container-fluid { padding-left:8px; padding-right:8px; }
    .card { border-radius:12px; }
  }
</style>

<nav class="navbar navbar-light bg-light sticky-top border-bottom">
  <div class="container-fluid">
    <span class="navbar-brand mb-0 h6">Schedule</span>
    <div class="d-flex flex-wrap ml-auto">
      <button class="btn btn-primary btn-sm mb-2" id="addBtn" type="button">Добавить</button>
    </div>
  </div>
</nav>

<div class="container-fluid my-3">
  <div class="row">
    <div class="col-12">
      <div class="card shadow-sm">
        <div class="card-header py-2 d-flex justify-content-between">
          <div class="small text-muted">План на неделю</div>
          <div class="small text-muted d-none d-sm-block">Двойной клик — создать событие</div>
        </div>
        <div class="card-body p-0">
          <div class="calendar-wrap" id="calWrap">
            <div class="calendar" id="calendar">
              <div class="day-head" id="dayHead">
                <div class="bg-light">Время</div>
                <div>Mon</div><div>Tue</div><div>Wed</div><div>Thu</div><div>Fri</div><div>Sat</div><div>Sun</div>
              </div>

              <div class="grid" id="grid">
                <div class="times bg-white" id="times"></div>
                <div class="day"><div class="day-body" data-day="1"></div></div>
                <div class="day"><div class="day-body" data-day="2"></div></div>
                <div class="day"><div class="day-body" data-day="3"></div></div>
                <div class="day"><div class="day-body" data-day="4"></div></div>
                <div class="day"><div class="day-body" data-day="5"></div></div>
                <div class="day"><div class="day-body" data-day="6"></div></div>
                <div class="day"><div class="day-body" data-day="7"></div></div>
                <div class="now-line" id="nowLine"></div>
              </div>
            </div>

            <!-- табы идут СРАЗУ после .calendar -->
            <div class="day-tabs" id="dayTabs">
              <button class="btn btn-outline-secondary btn-sm" data-day="1">Mon</button>
              <button class="btn btn-outline-secondary btn-sm" data-day="2">Tue</button>
              <button class="btn btn-outline-secondary btn-sm" data-day="3">Wed</button>
              <button class="btn btn-outline-secondary btn-sm" data-day="4">Thu</button>
              <button class="btn btn-outline-secondary btn-sm" data-day="5">Fri</button>
              <button class="btn btn-outline-secondary btn-sm" data-day="6">Sat</button>
              <button class="btn btn-outline-secondary btn-sm" data-day="7">Sun</button>
            </div>
          </div> <!-- /.calendar-wrap -->
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modal -->
<div class="modal fade" id="eventModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalTitle">Событие</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="evId" />
        <div class="form-row mb-2">
          <div class="col-6 col-md-4">
            <label class="form-label d-block">День</label>
            <select id="evDay" class="custom-select">
              <option value="1">Mon</option><option value="2">Tue</option><option value="3">Wed</option>
              <option value="4">Thu</option><option value="5">Fri</option><option value="6">Sat</option><option value="7">Sun</option>
            </select>
          </div>
          <div class="col-6 col-md-4">
            <label class="form-label d-block">Начало</label>
            <input id="evStart" class="form-control" type="time" step="1800" max="23:30" />
          </div>
          <div class="col-6 col-md-4 mt-2 mt-md-0">
            <label class="form-label d-block">Конец</label>
            <input id="evEnd" class="form-control" type="time" step="1800" max="23:30" />
          </div>
        </div>
        <div class="mb-2">
          <label class="form-label d-block">Заголовок</label>
          <input id="evTitle" class="form-control" placeholder="Например: Английский" />
        </div>
        <div class="mb-2">
          <label class="form-label d-block">Заметка</label>
          <input id="evNote" class="form-control" placeholder="Аудитория / группа" />
        </div>
        <div class="mb-2">
          <label class="form-label d-block">Цвет</label>
          <select id="evColor" class="custom-select">
            <option value="blue">Синий</option><option value="green">Зелёный</option><option value="purple">Фиолетовый</option><option value="orange">Оранжевый</option>
          </select>
        </div>
      </div>
      <div class="modal-footer justify-content-between">
        <button type="button" class="btn btn-outline-danger" id="deleteBtn">Удалить</button>
        <div>
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Отмена</button>
          <button type="button" class="btn btn-primary" id="saveBtn">Сохранить</button>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
(() => {
  'use strict';

  // ===== Config
  const START_TIME = "11:30", END_TIME = "23:30", SLOT_MIN = 30;
  const SLOT_PX = parseInt(getComputedStyle(document.documentElement).getPropertyValue('--slot-px')) || 30;
  const GUTTER_PX = 4;

  const qs  = (sel, root=document) => root.querySelector(sel);
  const qsa = (sel, root=document) => Array.from(root.querySelectorAll(sel));
  const toMinutes = t=>{const [h,m]=t.split(":").map(Number);return h*60+m};
  const minutesBetween=(a,b)=>toMinutes(b)-toMinutes(a);
  const pad2 = n => String(n).padStart(2,'0');

  // === измеряем высоты и прокидываем в CSS-переменные
  function measureOffsets(){
    const navbar = document.querySelector('.navbar');
    const head   = document.getElementById('dayHead');
    const navbarH = navbar ? navbar.getBoundingClientRect().height : 0;
    const headH   = head   ? head.getBoundingClientRect().height   : 40;
    document.documentElement.style.setProperty('--navbar-h', `${Math.round(navbarH)}px`);
    document.documentElement.style.setProperty('--head-h',   `${Math.round(headH)}px`);
  }
  measureOffsets();
  window.addEventListener('resize', measureOffsets);

  function uid(){ return Math.random().toString(36).slice(2)+Date.now().toString(36); }
  function loadEvents(){
    try{ const raw=localStorage.getItem('events'); if(raw) return JSON.parse(raw);}catch(e){}
    return [
      { id:uid(), day:1, start:"11:30", end:"12:30", title:"111", note:"1111", color:"blue" },
      { id:uid(), day:1, start:"11:30", end:"12:30", title:"3333", note:"3333", color:"green" },
      ...Array.from({length:16}, (_,i)=>({ id:uid(), day:1, start:"12:30", end:"13:30", title:String(i+1).padStart(2,'0'), note:"12:30", color:["green","purple","orange"][i%3]})),
      { id:uid(), day:2, start:"12:00", end:"13:00", title:"sdfsdf", note:"sdfsdf", color:"green" },
    ];
  }
  function saveEvents(){ localStorage.setItem('events', JSON.stringify(events)); }
  let events = loadEvents();

  (function buildTimes(){
    const timesEl=qs('#times');
    const rows=minutesBetween(START_TIME,END_TIME)/SLOT_MIN + 1;
    for(let i=0;i<rows;i++){
      const m=toMinutes(START_TIME)+i*SLOT_MIN;
      const hh=pad2(Math.floor(m/60));
      const mm=pad2(m%60);
      const div=document.createElement('div');
      div.className='time-cell';
      div.textContent=`${hh}:${mm}`;
      timesEl.appendChild(div);
    }
  })();

  function layoutDay(dayEvents){
    dayEvents.sort((a,b)=> (toMinutes(a.start)-toMinutes(b.start)) || (toMinutes(a.end)-toMinutes(b.end)));
    const clusters=[]; let cur=[]; let curMax=-1;
    for(const ev of dayEvents){
      const s=toMinutes(ev.start), e=toMinutes(ev.end);
      if(!cur.length || s<curMax){ cur.push(ev); curMax=Math.max(curMax,e); }
      else{ clusters.push(cur); cur=[ev]; curMax=e; }
    }
    if(cur.length) clusters.push(cur);

    const pos=new Map();
    for(const cl of clusters){
      const colsEnds=[], assigned=new Map();
      for(const ev of cl){
        const s=toMinutes(ev.start), e=toMinutes(ev.end);
        let col=0; while(col<colsEnds.length && colsEnds[col]>s) col++;
        colsEnds[col]=e; assigned.set(ev.id,col);
      }
      const total=colsEnds.length;
      for(const ev of cl) pos.set(ev.id,{col:assigned.get(ev.id), cols:total});
    }
    return pos;
  }

  function clearEvents(){ qsa('.day-body').forEach(el=>el.innerHTML=''); }
  function renderEvents(){
    clearEvents();
    for (let day = 1; day <= 7; day++) {
      const dayBody   = qs(`.day-body[data-day="${day}"]`);
      const dayWidth  = dayBody.clientWidth;
      const dayEvents = events.filter(e => e.day === day);
      const pos       = layoutDay(dayEvents);

      dayEvents.forEach(ev => {
        const top    = (minutesBetween(START_TIME, ev.start) / SLOT_MIN) * SLOT_PX;
        const height = Math.max(26, (minutesBetween(ev.start, ev.end) / SLOT_MIN) * SLOT_PX - 4);

        const { col, cols } = pos.get(ev.id);
        const totalGutter = GUTTER_PX * (cols - 1);
        const colWidth    = Math.floor((dayWidth - totalGutter) / cols);

        const leftPx  = col * (colWidth + GUTTER_PX);
        const widthPx = (col === cols - 1) ? (dayWidth - leftPx) : colWidth;

        const div = document.createElement('div');
        div.className = `event ${ev.color}`;
        div.style.top    = top + 'px';
        div.style.height = height + 'px';
        div.style.left   = leftPx + 'px';
        div.style.width  = Math.max(1, widthPx) + 'px';
        div.innerHTML = `<strong>${ev.title||'Без названия'}</strong>
                         <div class="small">${ev.start}–${ev.end}${ev.note?(' · '+ev.note):''}</div>`;
        div.addEventListener('click', (e)=>{ e.stopPropagation(); openEditor(ev); });
        dayBody.appendChild(div);
      });
    }
  }
  renderEvents();

  function updateNowLine(){
    const now=new Date();
    const curM=now.getHours()*60+now.getMinutes();
    const startM=toMinutes(START_TIME),endM=toMinutes(END_TIME);
    const within=curM>=startM&&curM<=endM;
    const y=((curM-startM)/SLOT_MIN)*SLOT_PX;
    const line=qs('#nowLine');
    line.style.display=within?'block':'none';
    if(within) line.style.top=y+'px';
  }
  updateNowLine(); setInterval(updateNowLine,60000);

  // dblclick to create
  qs('#grid').addEventListener('dblclick', (e)=>{
    const dayBody = e.target.closest('.day-body');
    if(!dayBody) return;

    const rect = dayBody.getBoundingClientRect();
    const y = e.clientY - rect.top;

    let startMin = toMinutes(START_TIME) + Math.round(y / SLOT_PX) * SLOT_MIN;
    const endLimit = toMinutes(END_TIME);

    let dur = Math.min(60, endLimit - startMin);
    if (dur < SLOT_MIN) {
      startMin = Math.max(toMinutes(START_TIME), endLimit - SLOT_MIN);
      dur = SLOT_MIN;
    }
    const endMin = startMin + dur;

    const start = `${pad2(Math.floor(startMin/60))}:${pad2(startMin%60)}`;
    const end   = `${pad2(Math.floor(endMin/60))}:${pad2(endMin%60)}`;

    openEditor({ id:null, day:Number(dayBody.dataset.day), start, end, title:'', note:'', color:'blue' });
  }, true);

  // ===== Modal editor (Bootstrap 4 via jQuery)
  function openEditor(ev){
    qs('#evId').value   = ev.id || '';
    qs('#evDay').value  = ev.day;
    qs('#evStart').value= ev.start;
    qs('#evEnd').value  = ev.end;
    qs('#evTitle').value= ev.title||'';
    qs('#evNote').value = ev.note||'';
    qs('#evColor').value= ev.color||'blue';
    qs('#deleteBtn').style.display = ev.id? 'inline-block':'none';
    qs('#modalTitle').textContent = ev.id? 'Редактировать событие' : 'Новое событие';
    $('#eventModal').modal('show');
  }
  document.getElementById('addBtn').addEventListener('click', ()=>{
    const day = isMobile() ? mobileDay : (new Date().getDay() || 7);
    openEditor({ id:null, day, start:'12:00', end:'13:00', title:'', note:'', color:'blue' });
  });
  document.getElementById('saveBtn').addEventListener('click', ()=>{
    const id   = qs('#evId').value || uid();
    const day  = Number(qs('#evDay').value);
    const start= qs('#evStart').value;
    const end  = qs('#evEnd').value;
    const title= qs('#evTitle').value.trim();
    const note = qs('#evNote').value.trim();
    const color= qs('#evColor').value;
    if(toMinutes(end) <= toMinutes(start)){ alert('Время окончания должно быть позже начала'); return; }
    const payload = { id, day, start, end, title, note, color };
    const idx = events.findIndex(x=>x.id===id);
    if(idx>=0) events[idx] = payload; else events.push(payload);
    saveEvents(); renderEvents(); $('#eventModal').modal('hide');
  });
  document.getElementById('deleteBtn').addEventListener('click', ()=>{
    const id = qs('#evId').value; if(!id) return;
    if(confirm('Удалить событие?')){
      events = events.filter(x=>x.id!==id);
      saveEvents(); renderEvents(); $('#eventModal').modal('hide');
    }
  });

  // ====== Resize day columns (desktop)
  const TIME_COL_PX = parseInt(getComputedStyle(document.documentElement).getPropertyValue('--time-col')) || 80;
  const MIN_DAY_PX  = 120;
  const head = qs('#dayHead');
  const grid = qs('#grid');

  let weights = loadDayWeights();
  applyColWidths();
  buildGrips();
  window.addEventListener('resize', () => { applyColWidths(); applyMobileMode(); });

  function loadDayWeights(){
    try{
      const raw = localStorage.getItem('dayWeights');
      if(raw){
        const arr = JSON.parse(raw);
        if(Array.isArray(arr) && arr.length===7){
          const s = arr.reduce((a,b)=>a+b,0);
          if(s>0) return arr.map(x=>x/s);
        }
      }
    }catch(e){}
    return Array(7).fill(1/7);
  }
  function saveDayWeights(){ localStorage.setItem('dayWeights', JSON.stringify(weights)); }

  function applyColWidths(){
    const calendar = qs('#calendar');
    if (calendar.classList.contains('mobile')) {
      renderEvents();
      return;
    }
    const total = head.clientWidth || head.getBoundingClientRect().width;
    const avail = Math.max(0, total - TIME_COL_PX);
    const base  = Math.min(MIN_DAY_PX, avail/7);
    const extra = Math.max(0, avail - base*7);
    const sumW  = weights.reduce((a,b)=>a+b,0) || 1;
    const colsPx = weights.map(w => base + extra*(w/sumW));
    const tpl = [TIME_COL_PX+"px", ...colsPx.map(v=>Math.round(v)+"px")].join(' ');
    head.style.gridTemplateColumns = tpl;
    grid.style.gridTemplateColumns = tpl;
    renderEvents();
  }

  function buildGrips(){
    head.querySelectorAll('.grip').forEach(n=>n.remove());
    for(let i=1;i<=6;i++){
      const cell = head.children[i];
      const g = document.createElement('span');
      g.className = 'grip';
      g.dataset.split = String(i);
      g.title = 'Потяни, чтобы изменить ширину дня';
      g.addEventListener('mousedown', startDrag);
      cell.appendChild(g);
    }
  }

  let drag=null;
  function startDrag(e){
    e.preventDefault();
    const i = Number(e.currentTarget.dataset.split);
    const total = head.clientWidth || head.getBoundingClientRect().width;
    const avail = Math.max(0, total - TIME_COL_PX);
    const base  = Math.min(MIN_DAY_PX, avail/7);
    const minFrac = base/Math.max(avail,1);

    drag = { i, startX:e.clientX, L:weights[i-1], R:weights[i], pair:weights[i-1]+weights[i], avail, minFrac };
    document.addEventListener('mousemove', onDrag);
    document.addEventListener('mouseup', endDrag);
    document.body.style.userSelect='none';
    document.body.style.cursor='col-resize';
  }
  function onDrag(e){
    if(!drag) return;
    const dx = e.clientX - drag.startX;
    const delta = dx / Math.max(drag.avail,1);
    let left = drag.L + delta;
    left = Math.max(drag.minFrac, Math.min(left, drag.pair - drag.minFrac));
    weights[drag.i-1] = left;
    weights[drag.i]   = drag.pair - left;
    applyColWidths();
  }
  function endDrag(){
    if(!drag) return;
    saveDayWeights();
    drag=null;
    document.removeEventListener('mousemove', onDrag);
    document.removeEventListener('mouseup', endDrag);
    document.body.style.userSelect='';
    document.body.style.cursor='';
  }

  // ===== Mobile one-day mode
  const calendar = qs('#calendar');
  const dayTabs  = qs('#dayTabs');
  let mobileDay = loadMobileDay() || getTodayWeekday();
  applyMobileMode(); // инициализация

  function getTodayWeekday(){ const d=new Date(); return (d.getDay()||7); }
  function loadMobileDay(){
    try { return Number(localStorage.getItem('mobileDay')) || null; } catch(e){ return null; }
  }
  function saveMobileDay(d){
    try { localStorage.setItem('mobileDay', String(d)); } catch(e){}
  }
  function isMobile(){ return window.innerWidth <= 576; }

  function setMobileDay(day){
    mobileDay = day;
    saveMobileDay(day);
    // активная кнопка
    qsa('[data-day]', dayTabs).forEach(btn=>{
      btn.classList.toggle('active', Number(btn.dataset.day)===day);
    });
    // показать нужный .day
    qsa('.grid .day').forEach(w=>w.classList.remove('show'));
    const showBody = qs(`.day-body[data-day="${day}"]`);
    if (showBody) showBody.parentElement.classList.add('show');
    // подсказка для хедера (Mon..Sun)
    for (let i=1;i<=7;i++) calendar.classList.remove(`show-col-${i}`);
    calendar.classList.add(`show-col-${day}`);
    renderEvents();
  }

  function applyMobileMode(){
    const wrap = document.getElementById('calWrap');
    if (isMobile()) {
      calendar.classList.add('mobile');
      setMobileDay(mobileDay || 1);
      // высота скролл-контейнера с учётом навбара
      measureOffsets();
      wrap.style.height = `calc(100dvh - var(--navbar-h))`;
    } else {
      calendar.classList.remove('mobile');
      qsa('.grid .day').forEach(w=>w.classList.remove('show'));
      for (let i=1;i<=7;i++) calendar.classList.remove(`show-col-${i}`);
      applyColWidths();
      measureOffsets();
      wrap.style.height = `calc(100dvh - var(--navbar-h))`;
    }
  }

  window.addEventListener('resize', applyMobileMode);
  dayTabs?.addEventListener('click', (e)=>{
    const btn = e.target.closest('[data-day]');
    if (!btn) return;
    setMobileDay(Number(btn.dataset.day));
  });

  // Demo nav
  document.getElementById('todayBtn').addEventListener('click', ()=> alert('Демо: переключение на текущую неделю'));
  document.getElementById('prevBtn').addEventListener('click', ()=> alert('Демо: предыдущая неделя'));
  document.getElementById('nextBtn').addEventListener('click', ()=> alert('Демо: следующая неделя'));
})();
</script>
@endsection
