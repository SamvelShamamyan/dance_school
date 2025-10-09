<!DOCTYPE html>
<html lang="hy">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Դրամարկղի մուտքի օրդեր</title>
<style>
  @page { size: A4; margin: 14mm; }
  @media print {
    body { margin: 0; }
    .divider { border-left: 1px dashed var(--line); }
    .btns { display:none !important; }
  }
  :root{
    --bg:#ffffff; --ink:#131416; --muted:#667085; --line:#CBD5E1;
    --primary:#2563eb; --accent:#e2ecff; --radius:10px;
    --font-main:"Inter","DejaVu Sans","Noto Sans Armenian",Arial,sans-serif;
    --font-title:"DejaVu Serif",Georgia,serif;
  }
  html, body{ height:100%; } 
  body{ margin:0; background:var(--bg); color:var(--ink); font-family:var(--font-main); line-height:1.42; }
  .btns{display:flex;gap:.6rem;padding:10px 14px;position:sticky;top:0;background:#f8fafc;border-bottom:1px solid var(--line);z-index:5;}
  .btns button{border:1px solid var(--line);background:#fff;padding:.5rem .8rem;border-radius:8px;cursor:pointer;font-weight:600;}
  .btns button.primary{background:var(--primary);border-color:var(--primary);color:#fff;}
  .sheet{padding:14mm;}
  .grid{display:grid;grid-template-columns:1fr 1px 1fr;gap:2mm;align-items:start;}
  .divider{height:100%;}
  .card{border:1px solid var(--line);border-radius:var(--radius);overflow:hidden;background:linear-gradient(180deg,#fff 0%,#fff 65%,#f9fbff 100%);box-shadow:0 0 0 3px #ffffff inset;}
  .card .stripe{height:8px;background:linear-gradient(90deg, var(--primary) 0%, #60a5fa 100%);}
  .card .inner{padding:12mm 11mm 10mm;}
  .header{text-align:center;margin-bottom:8mm;}
  .org{font-family:var(--font-title);font-weight:700;font-size:16px;letter-spacing:.2px;color:#0b3fb8;text-decoration:underline;text-underline-offset:3px;}
  .doctype{font-weight:900;font-size:18px;margin-top:3mm;}
  .row{display:grid;grid-template-columns:max-content 1fr;column-gap:8mm;align-items:end;margin:5mm 0;}
  .label{white-space:nowrap;font-weight:600;color:#1f2937;}
  .blank{display:inline-block;min-width:35mm;border-bottom:2px solid #0f172a;line-height:1.2;}
  .blank.short{min-width:20mm;}
  .blank.long{min-width:85mm;}
  .amount{display:grid;grid-template-columns:max-content 1fr max-content;column-gap:8mm;align-items:end;margin:5mm 0;}
  .meta{display:grid;grid-template-columns:repeat(3,max-content 1fr);column-gap:8mm;row-gap:4mm;margin-top:8mm;}
</style>
</head>
<body>
  <div class="btns">
    <button class="primary" style="margin-left:40px;" onclick="window.print()">Տպել</button>
  </div>

  <div class="sheet">
    <div class="grid">
      @php
        $org = optional($payment->school)->name ?? '';
        $fullName = $payment->student->first_name . ' ' . $payment->student->last_name;
        $groupLabel = optional($payment->group)->name ?? optional($payment->group)->id;
        $amount = number_format((float)$payment->amount, 0, '.', ',');
        $paymentType = $payment->method == 'cash' ? 'Կանխիկ' : 'Անկանխիկ';
        $purpose = 'Ուսման վարձ';
      @endphp               

      @foreach([1,2] as $i)
      <section class="card">
        <div class="stripe"></div>                      
        <div class="inner">
          <header class="header">
            <div class="org">Սոֆի Դևոյանի մշակույթի կենտրոն</div>
            <div class="doctype">{{ $org }}</div>
            <div class="doctype">Դրամարկղի մուտքի օրդեր</div>
          </header>

          <div class="row"><div class="label">№</div><div class="blank short">{{ $random_code }}</div></div>
          <div class="row"><div class="label">Ստացված է Անուն, Ազգանուն</div><div class="blank long">{{ $fullName }}</div></div>
          <div class="row"><div class="label">Հիմքը / Նպատակը</div><div class="blank long">{{ $purpose }}</div></div>
          <div class="row"><div class="label">Խմբի համարը</div><div class="blank short">{{ $groupLabel }}</div></div>

          <div class="amount">
            <div class="label">Գումարը</div>
            <div class="blank long">{{ $amount }}</div>
            <div>դրամ</div>
          </div>

          <div class="meta">
            <div class="label">Ամսաթիվ</div><div class="blank short">{{ $paid_date }}</div>
            <div class="label">Ժամ</div><div class="blank short">{{ $paid_time }}</div>
            <div class="label">Մեթոդ</div><div class="blank short">{{ strtoupper( $paymentType) }}</div>
          </div>
        </div>
      </section>

      @if ($i === 1)
        <div class="divider" style="border-left:1px dashed var(--line);"></div>
      @endif
      @endforeach
    </div>
  </div>

<script>
  (function(){
    const url = new URL(window.location.href);
    if (url.searchParams.get('print') === '1') {
      setTimeout(() => window.print(), 300);
    }
  })();
</script>
</body>
</html>
