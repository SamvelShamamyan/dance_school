(() => {
  'use strict'

/* =========================================================
   A) CONFIG & CONSTANTS
========================================================= */
  const START_TIME = "11:30", END_TIME = "23:30", SLOT_MIN = 30;
  const SLOT_PX    = parseInt(getComputedStyle(document.documentElement).getPropertyValue('--slot-px')) || 30;
  const GUTTER_PX  = 4;
  const TIME_COL_PX= parseInt(getComputedStyle(document.documentElement).getPropertyValue('--time-col')) || 80;
  const MIN_DAY_PX = 120;
  const isMobile   = () => window.innerWidth <= 1500;

  const ROLE = (window.currentUserRole || '').toString();
  const ROLE_SCHOOL_ID = window.currentUserRoleSchoolId ?? null;

  const API = {
    list:   '/admin/scheduleGroup/list',              // GET
    create: '/admin/scheduleGroup/add',               // POST
    update: id => `/admin/scheduleGroup/edit/${id}`,  // PUT
    delete: id => `/admin/scheduleGroup/delete/${id}` // DELETE
  };

/* =========================================================
   B) HELPERS (DOM, DATE/TIME, CSRF, FETCH)
========================================================= */
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
    const d = new Date(isoStr+"T00:00:00Z");
    d.setUTCDate(d.getUTCDate()+n);
    return formatISO(d);
  }
  function getCSRF(){
    const m = document.querySelector('meta[name="csrf-token"]');
    return m ? m.getAttribute('content') : '';
  }

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

  function buildFilterQuery() {
    const $wrap = $('#schedulerGroupFilter');
    if ($wrap.length === 0) return '';
    const schoolId = $wrap.find('#school_id').val() || '';
    const groupId  = $wrap.find('#group_id').val()  || '';
    const qp = new URLSearchParams();
    if (schoolId) qp.append('school_id', schoolId);
    if (groupId)  qp.append('group_id',  groupId);
    const s = qp.toString();
    return s ? `?${s}` : '';
  }

  // ======== ВАЛИДАЦИЯ (UI) ========
  function clearAllFieldErrors(root = document) {
    qsa('.is-invalid', root).forEach(el => el.classList.remove('is-invalid'));
    qsa('.invalid-feedback', root).forEach(el => el.remove());
    qsa('.error_school_id, .error_group_id, .error_room_id', root).forEach(el => el.textContent = '');
  }

  function setFieldError(selector, msg) {
    const el = qs(selector);
    if (!el) return;
    el.classList.add('is-invalid');
    const underSmall =
      el.closest('.form-group')?.querySelector('small[class^="error_"]') || null;
    if (underSmall) { underSmall.textContent = msg; return; }
    const fb = document.createElement('div');
    fb.className = 'invalid-feedback d-block';
    fb.textContent = msg;
    el.insertAdjacentElement('afterend', fb);
  }

  function renderValidationErrors(errors) {
    const candidates = {
      day:       ['#evDay'],
      start:     ['#evStart'],
      end:       ['#evEnd'],
      title:     ['#evTitle'],
      note:      ['#evNote'],
      color:     ['#evColor'],
      school_id: ['#eventModal #school_id'],
      group_id:  ['#eventModal #group_id', '#eventModal #group_id_s'],
      room_id:   ['#eventModal #room_id',  '#eventModal #room_id_s'],
    };

    clearAllFieldErrors(qs('#eventModal'));

    Object.entries(errors || {}).forEach(([field, msgs]) => {
      const msg = Array.isArray(msgs) ? msgs[0] : String(msgs || 'Սխալ');
      const list = candidates[field] || [];
      const sel = list.find(s => !!qs(s));
      if (sel) setFieldError(sel, msg);
    });
  }

  // async function apiFetch(url, opts={}){
  //   const headers = { 'Accept': 'application/json' };
  //   if(opts.method && opts.method !== 'GET'){
  //     headers['Content-Type'] = 'application/json';
  //     const csrf = getCSRF();
  //     if(csrf) headers['X-CSRF-TOKEN'] = csrf;
  //   }
  //   const res = await fetch(url, { credentials:'same-origin', ...opts, headers:{...headers, ...(opts.headers||{})} });

  //   if (res.status === 422) {
  //     let data = {};
  //     try { data = await res.json(); } catch {}
  //     const errors = (data && data.errors) ? data.errors : {};
  //     const message = data.message || 'Validation error';
  //     const err = new Error(message);
  //     err.isValidation = true;
  //     err.status = 422;
  //     err.errors = errors;
  //     throw err;
  //   }

  //   if(!res.ok){
  //     const text = await res.text().catch(()=> '');
  //     throw new Error(`HTTP ${res.status}: ${text || res.statusText}`);
  //   }
  //   if(res.status === 204) return null;
  //   return await res.json();
  // }

  async function apiFetch(url, opts = {}) {
    const headers = { 'Accept': 'application/json' };
    if (opts.method && opts.method !== 'GET') {
      headers['Content-Type'] = 'application/json';
      const csrf = getCSRF();
      if (csrf) headers['X-CSRF-TOKEN'] = csrf;
    }

    const res = await fetch(url, {
      credentials: 'same-origin',
      ...opts,
      headers: { ...headers, ...(opts.headers || {}) }
    });

    // Попробуем прочитать тело один раз
    let raw = '';
    let data = null;
    try {
      raw = await res.text();
      data = raw ? JSON.parse(raw) : null;
    } catch { /* игнор */ }

    // Валидация (422)
    if (res.status === 422) {
      const errors = (data && data.errors) || {};
      const message = (data && data.message) || 'Validation error';
      const err = new Error(message);
      err.isValidation = true;
      err.status = 422;
      err.errors = errors;
      throw err;
  }

  // Любая не-2xx ошибка
  if (!res.ok) {
    const err = new Error((data && (data.message || data.error)) || res.statusText || `HTTP ${res.status}`);
    err.status = res.status;
    err.data = data;
    throw err;
  }

  // На случай, если бэкенд вернул 200, но в теле {status:0, message:...}
  if (data && typeof data === 'object' && data.status === 0) {
    const err = new Error(data.message || 'Request failed');
    err.status = 400; // или 200, если хочешь строго; главное — чтобы был статус
    err.data = data;
    throw err;
  }

  return data;
}


  async function apiGetEvents() {
    const qs = buildFilterQuery();
    return apiFetch(`${API.list}${qs}`);
  }
  async function apiCreateEvent(payload) { return apiFetch(API.create, { method:'POST', body: JSON.stringify(payload) }); }
  async function apiUpdateEvent(id, payload) { return apiFetch(API.update(id), { method:'PUT', body: JSON.stringify(payload) }); }
  async function apiDeleteEvent(id) { return apiFetch(API.delete(id), { method:'DELETE' }); }

/* =========================================================
   C) STATE & DOM REFERENCES
========================================================= */
  let events = [];
  let weights = Array(7).fill(1/7);
  let mobileDay = getTodayWeekday();
  let weekStartISO = formatISO(startOfISOWeek(new Date()));

  const calendar = qs('#calendar');
  const head     = qs('#dayHead');
  const grid     = qs('#grid');
  const dayTabs  = qs('#dayTabs');
  const nowLine  = qs('#nowLine') || null;
  const addBtn   = qs('#addBtn');
  const wrap     = qs('#calWrap');

/* =========================================================
   D) LAYOUT
========================================================= */
  function measureOffsets(){
    const headEl = document.getElementById('dayHead');
    const footer = document.querySelector('.main-footer');
    const headH   = headEl ? Math.round(headEl.getBoundingClientRect().height) : 40;
    const footerH = footer ? Math.round(footer.getBoundingClientRect().height) : 0;
    document.documentElement.style.setProperty('--navbar-h', `0px`);
    document.documentElement.style.setProperty('--head-h',   `${headH}px`);
    document.documentElement.style.setProperty('--footer-h', `${footerH}px`);
    fitCalendarToViewport();
  }

  function fitCalendarToViewport(){
    if (!wrap) return;
    const footer = document.querySelector('.main-footer');
    const footerH = footer ? Math.round(footer.getBoundingClientRect().height) : 0;
    const top = wrap.getBoundingClientRect().top;
    const available = Math.max(160, Math.floor(window.innerHeight - footerH - top));
    wrap.style.maxHeight = available + 'px';
  }

  window.addEventListener('resize', () => {
    measureOffsets();
    if (!calendar.classList.contains('mobile')) {
      applyColWidths();
    }
    applyMobileMode();
  });
  document.addEventListener('shown.lte.pushmenu', () => {
    setTimeout(() => { measureOffsets(); applyColWidths(); }, 300);
  });
  document.addEventListener('collapsed.lte.pushmenu', () => {
    setTimeout(() => { measureOffsets(); applyColWidths(); }, 300);
  });

/* =========================================================
   E) TIME SCALE
========================================================= */
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

/* =========================================================
   F) WEEK HEADER
========================================================= */
  function applyWeekHeader(){
    for(let i=1;i<=7;i++){
      const dISO = addDays(weekStartISO, i-1);
      void new Date(dISO+"T00:00:00Z");
      const map = {1:'Երկուշաբթի',2:'Երեքշաբթի',3:'Չորեքշաբթի',4:'Հինգշաբթի',5:'ՈՒրբաթ',6:'Շաբաթ',7:'Կիրակի'};
      const el = qs(`[data-head-day="${i}"]`);
      if(el) el.textContent = `${map[i]}`;
    }
  }

/* =========================================================
   G) EVENT LAYOUT
========================================================= */
  function layoutDay(dayEvents){
    dayEvents.sort((a,b)=>
      (toMinutesSafe(a.start)-toMinutesSafe(b.start)) ||
      (toMinutesSafe(a.end)-toMinutesSafe(b.end))
    );
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

/* =========================================================
   H) RENDER
========================================================= */
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

        const titleText = `${ev.school_name ? ev.school_name + ' · ' : ''}${ev.title || ''}${ev.group_name ? ' (' + ev.group_name + ')' : ''}`;
        const subLine   = `${ev.start}–${ev.end}${ev.room_name ? ' · ' + ev.room_name : ''}`;

        div.innerHTML = `<strong>${titleText}</strong><div class="small">${subLine}</div>`;
        div.addEventListener('click', (e)=>{ e.stopPropagation(); openEditor(ev); });
        dayBody.appendChild(div);
      });
    }
    updateNowLine();
  }

/* =========================================================
   I) NOW LINE
========================================================= */
  function updateNowLine(){
    if (!nowLine) return;
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

/* =========================================================
   J) MODAL EDITOR
========================================================= */
  function resetModalHard($modal){
    const xhr = $modal.data('xhr');
    if (xhr && xhr.readyState !== 4) { try { xhr.abort(); } catch(_){} }
    $modal.removeData('xhr').removeData('prefGroup').removeData('prefRoom');

    const form = $modal.find('form')[0];
    if (form) form.reset();

    // супер-админ
    const $schoolSel = $modal.find('#school_id');
    const $groupSel  = $modal.find('#group_id');
    const $roomSel   = $modal.find('#room_id');
    $schoolSel.val('');
    $groupSel.empty().append('<option value="">Ընտրել</option>').prop('disabled', true);
    $roomSel.empty().append('<option value="">Ընտրել</option>').prop('disabled', true);

    // school-admin
    const $groupSelS = $modal.find('#group_id_s');
    const $roomSelS  = $modal.find('#room_id_s');
    if ($groupSelS.length) $groupSelS.val('');
    if ($roomSelS.length)  $roomSelS.val('');
  }

  function openEditor(ev){
    const $modal     = $('#eventModal');
    clearAllFieldErrors($modal[0]);

    const $schoolSel = $modal.find('#school_id');
    const $groupSel  = $modal.find('#group_id');
    const $roomSel   = $modal.find('#room_id');

    const prevXhr = $modal.data('xhr');
    if (prevXhr && prevXhr.readyState !== 4) { try { prevXhr.abort(); } catch(_){} }
    $modal.removeData('xhr');

    // base fields
    qs('#evId').value   = ev.id || '';
    qs('#evDay').value  = ev.day;
    qs('#evStart').value= normTime(ev.start) || '';
    qs('#evEnd').value  = normTime(ev.end) || '';
    qs('#evTitle').value= ev.title||'';
    qs('#evNote').value = ev.note ||'';
    qs('#evColor').value= ev.color||'blue';

    if (ROLE === 'super-admin' || ROLE === 'super-accountant') {
      // target school: сначала из ev.school_id (edit), иначе из фильтра (add)
      let targetSchoolId = ev.school_id != null ? String(ev.school_id) : '';
      if (!targetSchoolId && !ev.id) {
        const filterSchool = $('#schedulerGroupFilter #school_id').val();
        if (filterSchool) targetSchoolId = String(filterSchool);
      }

      if (ev.group_id) $modal.data('prefGroup', String(ev.group_id));
      if (ev.room_id)  $modal.data('prefRoom',  String(ev.room_id));

      if (targetSchoolId) {
        $schoolSel.val(targetSchoolId);
        $schoolSel.trigger('change'); // ajax подгрузит списки и выставит pref*
      } else {
        $schoolSel.val('');
        $groupSel.empty().append('<option value="">Ընտրել</option>').prop('disabled', true);
        $roomSel.empty().append('<option value="">Ընտրել</option>').prop('disabled', true);
        $modal.removeData('prefGroup').removeData('prefRoom');
      }
    } else if (ROLE === 'school-admin') {
      const gs = $modal.find('#group_id_s');
      const rs = $modal.find('#room_id_s');
      if (gs.length) gs.val(ev.group_id ? String(ev.group_id) : '');
      if (rs.length) rs.val(ev.room_id  ? String(ev.room_id)  : '');
    }

    const delBtn = qs('#deleteBtn');
    if (delBtn) delBtn.style.display = ev.id ? 'inline-block' : 'none';
    qs('#modalTitle').textContent = ev.id ? 'Խմբագրել դասաժամը' : 'Ավելցնել դասաժամ';

    $modal.modal('show');
  }

/* =========================================================
   K) USER INTERACTIONS
========================================================= */
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

  if (addBtn) addBtn.addEventListener('click', ()=>{
    const day = isMobile() ? mobileDay : getTodayWeekday();
    const filterSchool = $('#schedulerGroupFilter #school_id').val();
    openEditor({
      id: null,
      day,
      start:'12:00',
      end:'13:00',
      title:'',
      note:'',
      color:'blue',
      // хинт школе для super-admin при создании
      school_id: filterSchool ? Number(filterSchool) : null
    });
  });

/* =========================================================
   L) SAVE / DELETE
========================================================= */
  qs('#saveBtn').addEventListener('click', async ()=>{
    try{
      clearAllFieldErrors(qs('#eventModal'));

      const id   = qs('#evId').value || null;
      const day  = Number(qs('#evDay').value);
      const start= normTime(qs('#evStart').value);
      const end  = normTime(qs('#evEnd').value);
      const title= qs('#evTitle').value.trim();
      const note = qs('#evNote').value.trim();
      const color= qs('#evColor').value;

      let schoolId = null, groupId = null, roomId = null;

      if (ROLE === 'super-admin' || ROLE === 'super-accountant') {
        schoolId = qs('#eventModal #school_id')?.value ? Number(qs('#eventModal #school_id').value) : null;
        groupId  = qs('#eventModal #group_id')?.value  ? Number(qs('#eventModal #group_id').value)  : null;
        roomId   = qs('#eventModal #room_id')?.value   ? Number(qs('#eventModal #room_id').value)   : null;

        if (!schoolId) {
          setFieldError('#eventModal #school_id', 'Ընտրել ուս․ հաստատություն պարտադիր է');
          return;
        }
      } else if (ROLE === 'school-admin') {
        schoolId = ROLE_SCHOOL_ID ? Number(ROLE_SCHOOL_ID) : null;
        groupId  = qs('#eventModal #group_id_s')?.value ? Number(qs('#eventModal #group_id_s').value) : null;
        roomId   = qs('#eventModal #room_id_s')?.value  ? Number(qs('#eventModal #room_id_s').value)  : null;
      } else {
        groupId  = qs('#eventModal #group_id_s')?.value ? Number(qs('#eventModal #group_id_s').value) : null;
        roomId   = qs('#eventModal #room_id_s')?.value  ? Number(qs('#eventModal #room_id_s').value)  : null;
      }

      if(!start){ setFieldError('#evStart','Պարտադիր դաշտ'); return; }
      if(!end){ setFieldError('#evEnd','Պարտադիր դաշտ'); return; }
      if((toMinutesSafe(end) ?? 0) <= (toMinutesSafe(start) ?? 0)){
        setFieldError('#evEnd','Ավարտը պետք է լինի ավելի ուշ, քան սկիզբը');
        return;
      }

      const payload = {
        day, start, end, title, note, color,
        week_start: weekStartISO,
        school_id: schoolId,
        group_id:  groupId,
        room_id:   roomId,
      };

      if(id){
        await apiUpdateEvent(id, payload);
        swal("success", 'Գործողությունը կատարված է։');
      } else {
        await apiCreateEvent(payload);
        swal("success", 'Գործողությունը կատարված է։');
      }

      await reloadEvents();
      $('#eventModal').modal('hide');
    }catch(err){
      const msg = err?.data?.message || err?.message || "Սխալ է տեղի ունեցել։ Խնդրում ենք կրկին próbել։";

      if (err.status === 409 || err?.data?.status === 0) {
        showInfo("info", msg, "");
        return;
      }

      if (err?.isValidation && err.status === 422) {
        renderValidationErrors(err.errors);
        return;
      }
      swal("error", "Սխալ է տեղի ունեցել։ Խնդրում ենք կրկին փորձել։");
    }
  });

  // qs('#deleteBtn').addEventListener('click', async ()=>{
  //   const id = qs('#evId').value; if(!id) return;
  //   if(confirm('Ցանկանում եք հեռացնել դասաժմը?')){
  //     try{
  //       await apiDeleteEvent(id);
  //       await reloadEvents();
  //       $('#eventModal').modal('hide');
  //     }catch(err){
  //       console.error(err);
  //       swal("error", "Սխալ է տեղի ունեցել։ Խնդրում ենք կրկին փորձել:", true, true)
  //     }
  //   }
  // });

  qs('#deleteBtn').addEventListener('click', async () => {
    const id = qs('#evId').value;
    if (!id) return;

    const result = await Swal.fire({
      title: "Դուք համոզված եք՞",
      text: "",
      icon: "danger",
      showDenyButton: true,
      showCancelButton: false,
      confirmButtonText: "Այո",
      denyButtonText: "Ոչ",
      reverseButtons: true
    });

    if (!result.isConfirmed) return;

    try {
      await apiDeleteEvent(id);      // DELETE /admin/scheduleGroup/delete/{id}
      await reloadEvents();
      $('#eventModal').modal('hide');

      if (typeof swal === 'function') {
        swal("success", "Գործողությունը կատարված է։");
      } else {
        Swal.fire("Պատրաստ է", "Գործողությունը կատարված է։", "success");
      }
    } catch (err) {
      const msg = (err && err.data && err.data.message) || err?.message || "Սխալ է տեղի ունեցել։ Խնդրում ենք կրկին փորձել։";
      if (typeof swal === 'function') {
        swal("error", msg);
      } else {
        Swal.fire("Սխալ", msg, "error");
      }
    }
  });


/* =========================================================
   M) DESKTOP COLUMN RESIZE
========================================================= */
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

/* =========================================================
   N) MOBILE MODE
========================================================= */
  function getTodayWeekday(){ const d=new Date(); return (d.getDay()||7); }

  function setMobileDay(day){
    mobileDay = day;
    if (dayTabs) {
      qsa('[data-day]', dayTabs).forEach(btn=> btn.classList.toggle('active', Number(btn.dataset.day)===day));
    }
    qsa('.grid .day').forEach(w=>w.classList.remove('show'));
    const showBody = qs(`.day-body[data-day="${day}"]`);
    if (showBody) showBody.parentElement.classList.add('show');

    for (let i=1;i<=7;i++) calendar.classList.remove(`show-col-${i}`);
    calendar.classList.add(`show-col-${day}`);
    renderEvents();
  }

  function applyMobileMode(){
    const mobile = isMobile();
    if (mobile) {
      calendar.classList.add('mobile');
      setMobileDay(mobileDay || 1);
      measureOffsets();
      if (dayTabs) dayTabs.style.display = 'flex';
    } else {
      calendar.classList.remove('mobile');
      qsa('.grid .day').forEach(w=>w.classList.remove('show'));
      for (let i=1;i<=7;i++) calendar.classList.remove(`show-col-${i}`);
      buildGrips();
      applyColWidths();
      measureOffsets();
      if (dayTabs) dayTabs.style.display = 'none';
    }
  }

  dayTabs?.addEventListener('click', (e)=>{
    const btn = e.target.closest('[data-day]');
    if (!btn) return;
    setMobileDay(Number(btn.dataset.day));
  });

  // mobile→desktop guard
  let lastIsMobile = null;
  window.addEventListener('resize', () => {
    const isMob = calendar.classList.contains('mobile');
    if (lastIsMobile === null) lastIsMobile = isMob;
    if (lastIsMobile && !isMob) { buildGrips(); applyColWidths(); }
    lastIsMobile = isMob;
  });

/* =========================================================
   O) DATA LOAD
========================================================= */
  async function reloadEvents(){
    try{
      applyWeekHeader();
      const list = await apiGetEvents(); // GET с фильтрами

      events = (Array.isArray(list) ? list : []).map(x => {
        const schoolId = x.school_id ?? (x.school && (x.school.id ?? x.school.school_id)) ?? null;
        const groupId  = x.group_id  ?? (x.group  && (x.group.id  ?? x.group.group_id))  ?? null;
        const roomId   = x.room_id   ?? (x.room   && (x.room.id   ?? x.room.room_id))    ?? null;

        return {
          id: x.id,
          day: Number(x.week_day),            // 1..7
          start: normTime(x.start_time),
          end:   normTime(x.end_time),
          title: x.title || '',
          note:  x.note  || '',
          color: x.color || 'blue',

          school_id: schoolId,
          group_id:  groupId,
          room_id:   roomId,

          school_name: (x.school && (x.school.name ?? x.school.title)) || '',
          group_name:  (x.group  && (x.group.name  ?? x.group.title))  || '',
          room_name:   (x.room   && (x.room.name   ?? x.room.title))   || '',
        };
      }).filter(e => e.day>=1 && e.day<=7 && e.start && e.end);

      applyMobileMode(); // внутри вызовет renderEvents
    }catch(err){
      console.error(err);
      events = [];
      applyMobileMode();
      alert('Ошибка загрузки событий (GET). Проверьте API.');
    }
  }

/* =========================================================
   P) INIT
========================================================= */
  document.addEventListener('DOMContentLoaded', () => {
    measureOffsets();
    buildGrips();
    applyColWidths();
    reloadEvents();

    const $modal = $('#eventModal');
    $modal.on('hidden.bs.modal', function(){
      clearAllFieldErrors(this);
      resetModalHard($modal);
    });

    document.addEventListener('filters:changed', () => {
      reloadEvents();
    });

    window.addEventListener('load', measureOffsets);
  });

})(); // IIFE END

/* =========================================================
   Q) jQuery ZONE: dependent selects & header filters
========================================================= */
$(document).ready(function(){

  // --- Dependent selects inside modal (только для супер-ролей) ---
  $('#eventModal #school_id').on('change', function(){
    const $modal = $('#eventModal');
    const schoolId = $(this).val();

    let $groupSelect = $modal.find('#group_id');
    let $roomSelect  = $modal.find('#room_id');

    $groupSelect.empty().append('<option value="">Ընտրել</option>').prop('disabled', true);
    $roomSelect.empty().append('<option value="">Ընտրել</option>').prop('disabled', true);

    const prevXhr = $modal.data('xhr');
    if (prevXhr && prevXhr.readyState !== 4) { try { prevXhr.abort(); } catch(_){} }

    if (!schoolId) {
      $modal.removeData('prefGroup').removeData('prefRoom').removeData('xhr');
      return;
    }

    const xhr = $.ajax({
      headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
      url:  `/admin/scheduleGroup/getGroupsRoomsBySchool/${schoolId}`,
      type: 'GET',
      dataType: 'json',
      success: function(response) {
        const wantGroupId = String($modal.data('prefGroup') || '');
        const wantRoomId  = String($modal.data('prefRoom')  || '');

        $groupSelect.empty().append('<option value="">Ընտրել</option>');
        $roomSelect.empty().append('<option value="">Ընտրել</option>');

        $.each(response.groups, function (_, group) {
          $groupSelect.append($('<option>', { value: group.id, text: group.name }));
        });
        $.each(response.rooms, function (_, room) {
          $roomSelect.append($('<option>', { value: room.id, text: room.name }));
        });

        if (wantGroupId) $groupSelect.val(wantGroupId);
        if (wantRoomId)  $roomSelect.val(wantRoomId);

        $groupSelect.prop('disabled', false);
        $roomSelect.prop('disabled', false);

        $modal.removeData('prefGroup').removeData('prefRoom');
      },
      error: function() {
        swal("error", "Սխալ է տեղի ունեցել։ Խնդրում ենք կրկին փորձել։", "error");
      },
      complete: function(){
        const cur = $modal.data('xhr');
        if (cur === xhr) $modal.removeData('xhr');
      }
    });

    $modal.data('xhr', xhr);
  });

  // --- Header filter: school → groups ---
  $('#schedulerGroupFilter #school_id').on('change', function(){
    const schoolId = $(this).val();
    let $select = $('#group_id');

    if (!schoolId) {
      $select.prop('disabled', true).empty().append('<option value="">Բոլորը</option>');
      document.dispatchEvent(new Event('filters:changed'));
      return;
    }

    $.ajax({
      headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
      url:  `/admin/scheduleGroup/getGroupsRoomsBySchool/${schoolId}`,
      type: 'GET',
      dataType: 'json',
      success: function(response) {
        $select.prop('disabled',false);
        $select.empty().append('<option value="">Բոլորը</option>');
        $.each(response.groups, function (index, group) {
          $select.append($('<option>', { value: group.id, text: group.name }));
        });
        document.dispatchEvent(new Event('filters:changed'));
      },
      error: function() {
        swal("error", "Սխալ է տեղի ունեցել։ Խնդրում ենք կրկին փորձել։", "error");
      },
    });
  });

  // --- Header filter: group change ---
  $('#schedulerGroupFilter #group_id').on('change', function(){
    document.dispatchEvent(new Event('filters:changed'));
  });

});
