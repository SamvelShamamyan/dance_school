(() => {
  'use strict';

  /* ===========================
     CONFIG
  ============================*/
  const START_TIME = "11:30", END_TIME = "23:30", SLOT_MIN = 30;
  const SLOT_PX = parseInt(getComputedStyle(document.documentElement).getPropertyValue('--slot-px')) || 30;
  const GUTTER_PX = 4;
  const TIME_COL_PX = parseInt(getComputedStyle(document.documentElement).getPropertyValue('--time-col')) || 80;
  const MIN_DAY_PX  = 120;
  const isMobile = ()=> window.innerWidth <= 1500; // твой порог

  const API = {
    list:   '/admin/scheduleGroup/list',              // GET
    create: '/admin/scheduleGroup/add',               // POST
    update: id => `/admin/scheduleGroup/edit/${id}`,  // PUT
    delete: id => `/admin/scheduleGroup/delete/${id}` // DELETE
  };

  /* ===========================
     HELPERS
  ============================*/
  const qs  = (sel, root=document) => root.querySelector(sel);
  const qsa = (sel, root=document) => Array.from(root.querySelectorAll(sel));
  const pad2 = n => String(n).padStart(2,'0');

  function formatISO(d){ return d.toISOString().slice(0,10); }
  function startOfISOWeek(date){
    const d = new Date(Date.UTC(date.getFullYear(), date.getMonth(), date.getDate()));
    const day = d.getUTCDay() || 7; // Mon=1..Sun=7
    if(day>1) d.setUTCDate(d.getUTCDate() - (day-1));
    return d;
  }
  function addDays(isoStr, n){
    const d = new Date(isoStr+"T00:00:00Z"); d.setUTCDate(d.getUTCDate()+n); return formatISO(d);
  }
  function getCSRF(){
    const m = document.querySelector('meta[name="csrf-token"]'); return m ? m.getAttribute('content') : '';
  }

  // Safe time parse
  const normTime = t => {
    if (!t) return null;
    const s = String(t).trim();
    const m = s.match(/^(\d{2}):(\d{2})(?::\d{2})?$/);
    if (!m) return null;
    return `${m[1]}:${m[2]}`;
  };
  const toMinutesSafe = t => {
    const v = normTime(t);
    if (!v) return null;
    const [h,m] = v.split(':').map(Number);
    return h*60 + m;
  };
  const minutesBetween = (a,b) => {
    const am = toMinutesSafe(a), bm = toMinutesSafe(b);
    if (am == null || bm == null) return null;
    return bm - am;
  };

  async function apiFetch(url, opts={}){
    const headers = { 'Accept': 'application/json' };
    if(opts.method && opts.method !== 'GET'){
      headers['Content-Type'] = 'application/json';
      const csrf = getCSRF();
      if(csrf) headers['X-CSRF-TOKEN'] = csrf;
    }
    const res = await fetch(url, { credentials:'same-origin', ...opts, headers:{...headers, ...(opts.headers||{})} });
    if(!res.ok){
      const text = await res.text().catch(()=> '');
      throw new Error(`HTTP ${res.status}: ${text || res.statusText}`);
    }
    if(res.status === 204) return null;
    return await res.json();
  }

  async function apiGetEvents(weekStartISO) { return apiFetch(`${API.list}?week_start=${encodeURIComponent(weekStartISO)}`); }
  async function apiCreateEvent(payload) { return apiFetch(API.create, { method:'POST', body: JSON.stringify(payload) }); }
  async function apiUpdateEvent(id, payload) { return apiFetch(API.update(id), { method:'PUT', body: JSON.stringify(payload) }); }
  async function apiDeleteEvent(id) { return apiFetch(API.delete(id), { method:'DELETE' }); }

  /* ===========================
     STATE
  ============================*/
  let events = [];
  let weights = Array(7).fill(1/7);
  let mobileDay = getTodayWeekday();
  let weekStartISO = formatISO(startOfISOWeek(new Date()));

  const calendar = qs('#calendar');
  const head = qs('#dayHead');
  const grid = qs('#grid');
  const dayTabs  = qs('#dayTabs');
  const nowLine = qs('#nowLine');
  const addBtn = qs('#addBtn');

  /* ===========================
     Sticky vars (без navbar)
  ============================*/
  function measureOffsets(){
    const headEl = document.getElementById('dayHead');
    const headH   = headEl ? headEl.getBoundingClientRect().height : 40;
    document.documentElement.style.setProperty('--navbar-h', `0px`);
    document.documentElement.style.setProperty('--head-h',   `${Math.round(headH)}px`);
  }
  measureOffsets();
  window.addEventListener('resize', measureOffsets);

  /* ===========================
     Build time labels
  ============================*/
  (function buildTimes(){
    const timesEl=qs('#times');
    const rows = (minutesBetween(START_TIME,END_TIME) ?? 0)/SLOT_MIN + 1;
    for(let i=0;i<rows;i++){
      const m=(toMinutesSafe(START_TIME) ?? 0)+i*SLOT_MIN;
      const hh=pad2(Math.floor(m/60));
      const mm=pad2(m%60);
      const div=document.createElement('div');
      div.className='time-cell';
      div.textContent=`${hh}:${mm}`;
      timesEl.appendChild(div);
    }
  })();

  /* ===========================
     Week header (dates only)
  ============================*/
  function applyWeekHeader(){
    for(let i=1;i<=7;i++){
      const dISO = addDays(weekStartISO, i-1);
      const d = new Date(dISO+"T00:00:00Z");
      const map = {1:'Mon',2:'Tue',3:'Wed',4:'Thu',5:'Fri',6:'Sat',7:'Sun'};
      const el = qs(`[data-head-day="${i}"]`);
      if(el) el.textContent = `${map[i]} ${d.getUTCDate()}.${String(d.getUTCMonth()+1).padStart(2,'0')}`;
    }
  }

  /* ===========================
     Layout & Render
  ============================*/
  function layoutDay(dayEvents){
    dayEvents.sort((a,b)=> (toMinutesSafe(a.start)-toMinutesSafe(b.start)) || (toMinutesSafe(a.end)-toMinutesSafe(b.end)));
    const clusters=[]; let cur=[]; let curMax=-1;
    for(const ev of dayEvents){
      const s=toMinutesSafe(ev.start), e=toMinutesSafe(ev.end);
      if(!cur.length || s<curMax){ cur.push(ev); curMax=Math.max(curMax,e); }
      else{ clusters.push(cur); cur=[ev]; curMax=e; }
    }
    if(cur.length) clusters.push(cur);

    const pos=new Map();
    for(const cl of clusters){
      const colsEnds=[], assigned=new Map();
      for(const ev of cl){
        const s=toMinutesSafe(ev.start), e=toMinutesSafe(ev.end);
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

      dayEvents.sort((a, b) => {
        const s = toMinutesSafe(a.start) - toMinutesSafe(b.start);
        if (s) return s;
        return (toMinutesSafe(a.end)-toMinutesSafe(a.start)) - (toMinutesSafe(b.end)-toMinutesSafe(b.start));
      });

      dayEvents.forEach(ev => {
        const startDiff = minutesBetween(START_TIME, ev.start);
        const endDiff   = minutesBetween(START_TIME, ev.end);
        if (startDiff == null || endDiff == null) return;

        const top    = (startDiff / SLOT_MIN) * SLOT_PX;
        const height = Math.max(26, ((endDiff - startDiff) / SLOT_MIN) * SLOT_PX - 4);

        const { col, cols } = pos.get(ev.id);
        const totalGutter = GUTTER_PX * (cols - 1);
        const colWidth    = (dayWidth - totalGutter) / cols;
        const leftPx      = Math.round(col * colWidth + col * GUTTER_PX);
        const rightPx     = Math.round((col + 1) * colWidth + col * GUTTER_PX);
        const widthPx     = Math.max(1, rightPx - leftPx);

        const div = document.createElement('div');
        div.className = `event ${ev.color}`;
        div.style.top    = top + 'px';
        div.style.height = height + 'px';
        div.style.left   = leftPx + 'px';
        div.style.width  = widthPx + 'px';

        const titleText = (ev.title && ev.title.trim()) ? ev.title.trim() : `${ev.start}–${ev.end}`;
        const subLine   = ev.title ? `${ev.start}–${ev.end}${ev.note?(' · '+ev.note):''}` : (ev.note || '');

        div.innerHTML = `<strong>${titleText}</strong><div class="small">${subLine}</div>`;
        div.addEventListener('click', (e)=>{ e.stopPropagation(); openEditor(ev); });
        dayBody.appendChild(div);
      });
    }
    updateNowLine();
  }

  /* ===========================
     Now line
  ============================*/
  function updateNowLine(){
    const today = new Date();
    const todayISO = formatISO(startOfISOWeek(today));
    const isCurrentWeek = (todayISO === weekStartISO);
    if(!isCurrentWeek){ nowLine.style.display='none'; return; }

    const curM = today.getHours()*60 + today.getMinutes();
    const startM=toMinutesSafe(START_TIME), endM=toMinutesSafe(END_TIME);
    if (startM == null || endM == null) { nowLine.style.display='none'; return; }
    const within=curM>=startM&&curM<=endM;
    const y=((curM-startM)/SLOT_MIN)*SLOT_PX;

    nowLine.style.display=within?'block':'none';
    if(within) nowLine.style.top=y+'px';
  }
  setInterval(updateNowLine, 60000);

  /* ===========================
     Modal editor
  ============================*/
  function openEditor(ev){
    qs('#evId').value   = ev.id || '';
    qs('#evDay').value  = ev.day;
    qs('#evStart').value= normTime(ev.start) || '';
    qs('#evEnd').value  = normTime(ev.end) || '';
    qs('#evTitle').value= ev.title||'';
    qs('#evNote').value = ev.note||'';
    qs('#evColor').value= ev.color||'blue';
    qs('#deleteBtn').style.display = ev.id? 'inline-block':'none';
    qs('#modalTitle').textContent = ev.id? 'Редактировать событие' : 'Новое событие';
    $('#eventModal').modal('show');
  }

  // dblclick create
  qs('#grid').addEventListener('dblclick', (e)=>{
    const dayBody = e.target.closest('.day-body');
    if(!dayBody) return;
    const rect = dayBody.getBoundingClientRect();
    const y = e.clientY - rect.top;

    let startMin = (toMinutesSafe(START_TIME) ?? 0) + Math.round(y / SLOT_PX) * SLOT_MIN;
    const endLimit = toMinutesSafe(END_TIME) ?? (23*60+30);

    let dur = Math.min(60, endLimit - startMin);
    if (dur < SLOT_MIN) { startMin = Math.max((toMinutesSafe(START_TIME) ?? 0), endLimit - SLOT_MIN); dur = SLOT_MIN; }
    const endMin = startMin + dur;

    const start = `${pad2(Math.floor(startMin/60))}:${pad2(startMin%60)}`;
    const end   = `${pad2(Math.floor(endMin/60))}:${pad2(endMin%60)}`;

    openEditor({ id:null, day:Number(dayBody.dataset.day), start, end, title:'', note:'', color:'blue' });
  }, true);

  // add button (если есть)
  if (addBtn) addBtn.addEventListener('click', ()=>{
    const day = isMobile() ? mobileDay : getTodayWeekday();
    openEditor({ id:null, day, start:'12:00', end:'13:00', title:'', note:'', color:'blue' });
  });

  // save/delete
  qs('#saveBtn').addEventListener('click', async ()=>{
    try{
      const id   = qs('#evId').value || null;
      const day  = Number(qs('#evDay').value);
      const start= normTime(qs('#evStart').value);
      const end  = normTime(qs('#evEnd').value);
      const title= qs('#evTitle').value.trim();
      const note = qs('#evNote').value.trim();
      const color= qs('#evColor').value;

      if(!start || !end){ alert('Заполните время начала и конца'); return; }
      if((toMinutesSafe(end) ?? 0) <= (toMinutesSafe(start) ?? 0)){ alert('Время окончания должно быть позже начала'); return; }

      const payload = { day, start, end, title, note, color, week_start: weekStartISO };
      if(id){ await apiUpdateEvent(id, payload); }
      else{
        const created = await apiCreateEvent(payload);
        if(created && created.id) payload.id = created.id;
      }
      await reloadEvents();
      $('#eventModal').modal('hide');
    }catch(err){ console.error(err); alert('Ошибка сохранения события'); }
  });

  qs('#deleteBtn').addEventListener('click', async ()=>{
    const id = qs('#evId').value; if(!id) return;
    if(confirm('Удалить событие?')){
      try{ await apiDeleteEvent(id); await reloadEvents(); $('#eventModal').modal('hide'); }
      catch(err){ console.error(err); alert('Ошибка удаления'); }
    }
  });

  /* ===========================
     Column resize (desktop)
  ============================*/
  function applyColWidths(){
    if (calendar.classList.contains('mobile')) { renderEvents(); return; }
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
    const headEl = document.getElementById('dayHead');
    if (!headEl || headEl.children.length < 8) return;
    headEl.querySelectorAll('.grip').forEach(n=>n.remove());
    for(let i=1;i<=6;i++){
      const cell = headEl.children[i];
      if (!cell) continue;
      cell.style.position = 'relative';
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
    drag=null;
    document.removeEventListener('mousemove', onDrag);
    document.removeEventListener('mouseup', endDrag);
    document.body.style.userSelect='';
    document.body.style.cursor='';
  }

  function getTodayWeekday(){ const d=new Date(); return (d.getDay()||7); }

  /* ===========================
     Mobile one-day mode
  ============================*/
  function setMobileDay(day){
    mobileDay = day;
    qsa('[data-day]', dayTabs).forEach(btn=> btn.classList.toggle('active', Number(btn.dataset.day)===day));
    qsa('.grid .day').forEach(w=>w.classList.remove('show'));
    const showBody = qs(`.day-body[data-day="${day}"]`);
    if (showBody) showBody.parentElement.classList.add('show');
    for (let i=1;i<=7;i++) calendar.classList.remove(`show-col-${i}`);
    calendar.classList.add(`show-col-${day}`);
    renderEvents();
  }

  function applyMobileMode(){
    const wrap = document.getElementById('calWrap');
    if (isMobile()) {
      calendar.classList.add('mobile');
      setMobileDay(mobileDay || 1);
      measureOffsets();
      wrap.style.height = `calc(100dvh - var(--navbar-h))`;
    } else {
      calendar.classList.remove('mobile');
      qsa('.grid .day').forEach(w=>w.classList.remove('show'));
      for (let i=1;i<=7;i++) calendar.classList.remove(`show-col-${i}`);
      buildGrips();
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

  // переход mobile -> desktop (страховка)
  let lastIsMobile = null;
  window.addEventListener('resize', () => {
    const isMob = calendar.classList.contains('mobile');
    if (lastIsMobile === null) lastIsMobile = isMob;
    if (lastIsMobile && !isMob) { buildGrips(); applyColWidths(); }
    lastIsMobile = isMob;
  });

  /* ===========================
     Load + render
  ============================*/
  async function reloadEvents(){
    try{
      applyWeekHeader();
      const list = await apiGetEvents(weekStartISO);

      events = (Array.isArray(list) ? list : []).map(x => ({
        id: x.id,
        day: Number(x.week_day),            // 1..7
        start: normTime(x.start_time),
        end:   normTime(x.end_time),
        title: x.title || '',
        note:  x.note  || '',
        color: x.color || 'blue',
      })).filter(e => e.day>=1 && e.day<=7 && e.start && e.end);

      applyMobileMode(); // внутри вызовет renderEvents
    }catch(err){
      console.error(err);
      events = [];
      applyMobileMode();
      alert('Ошибка загрузки событий (GET). Проверьте API.');
    }
  }

  // при готовности DOM
  window.addEventListener('DOMContentLoaded', () => {
    if (!calendar.classList.contains('mobile')) {
      buildGrips();
      applyColWidths();
    }
  });

  // Initial load
  reloadEvents();

})();