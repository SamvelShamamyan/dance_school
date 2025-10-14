<!DOCTYPE html>
<html lang="hy">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Դրամարկղի մուտքի օրդեր</title>
<style>
  @page { size: A4; margin: 16mm 18mm; }
  * { -webkit-print-color-adjust: exact; print-color-adjust: exact; }

  :root{
    --bg:#fff; --ink:#111827; --muted:#6b7280; --line:#CBD5E1; --ink-strong:#0f172a;
    --primary:#2563eb; --radius:10px;
    --font-main:"Inter","DejaVu Sans","Noto Sans Armenian",Arial,sans-serif;
    --font-title:"DejaVu Serif",Georgia,serif;
  }

  html, body { height:auto; }
  body{
    margin:0; background:var(--bg); color:var(--ink);
    font-family:var(--font-main); font-size:12px; line-height:1.35;
    font-variant-numeric: tabular-nums;
  }

  .btns{display:flex;gap:.6rem;padding:10px 14px;position:sticky;top:0;background:#f8fafc;border-bottom:1px solid var(--line);z-index:5;}
  .btns button{border:1px solid var(--line);background:#fff;padding:.5rem .8rem;border-radius:8px;cursor:pointer;font-weight:600;}
  .btns button.primary{background:var(--primary);border-color:var(--primary);color:#fff;}
  @media print { .btns{display:none !important;} .card{page-break-inside:avoid;} }

  .sheet{width:100%; margin:0 auto; padding:0; box-sizing:border-box;}

  .grid{
    margin-top: 50px;
    display:grid;
    grid-template-columns: 84mm 0 84mm; 
    column-gap: 4mm;                   
    justify-content:center;
    align-items:start;
  }
  .divider{
    width:0; border-left:1px dashed var(--line); align-self:stretch;
  }

  .card{
    width:84mm; 
    border:1px solid var(--line); border-radius:var(--radius);
    background:#fff; overflow:hidden;
  }
  .stripe{height:6px;background:linear-gradient(90deg, var(--primary) 0%, #60a5fa 100%);}

  .inner{padding:8mm 6mm 7mm;} 

  .header{text-align:center;margin-bottom:5mm;} 
  .org{font-family:var(--font-title);font-weight:700;font-size:14px;color:#0b3fb8;text-decoration:underline;text-underline-offset:3px;}
  .doctype{font-weight:800;font-size:13px;margin-top:1.6mm;}

  .field{
    display:grid;
    grid-template-columns: max-content auto max-content;
    column-gap:4mm;          
    align-items:flex-end;
    margin:3.2mm 0;          
  }
  .field .label{white-space:nowrap;font-weight:600;color:#1f2937;}
  .value{position:relative;min-height:1.1em;}
  .value span{display:inline;white-space:normal;word-break:break-word;}
  .value::after{
    content:""; position:absolute; left:0; right:0; bottom:-1px;
    height:0; border-bottom:1.4px solid var(--ink-strong); 
  }
  .w-short{width:28mm;}
  .w-long{width:64mm;}
  .w-amt{width:64mm;}
  .suffix{margin-left:2mm;}

  .field.stack{
    grid-template-columns: 1fr;
    row-gap: 2mm;
    align-items:start;
  }
  .field.stack .suffix{display:none;}
  .field.stack .label{margin:0;}

  .meta .field{margin:3mm 0;}
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
            <div class="doctype">Դպրոց - {{ $org }}</div>
            <div class="doctype">Դրամարկղի մուտքի օրդեր</div>
          </header>

          <div class="field ">
            <div class="label">№</div>
            <div class="value w-amt"><span>{{ $random_code }}</span></div>
            <div class="suffix"></div>
          </div>

          <div class="field stack">
            <div class="label">Ստացված է Անուն, Ազգանուն</div>
            <div class="value w-amt"><span>{{ $fullName }}</span></div>
            <div class="suffix"></div>
          </div>

          <div class="field ">
            <div class="label">Հիմքը / Նպատակը</div>
            <div class="value w-amt"><span>{{ $purpose }}</span></div>
            <div class="suffix"></div>
          </div>

          <div class="field ">
            <div class="label">Խմբի համարը</div>
            <div class="value w-amt"><span>{{ $groupLabel }}</span></div>
            <div class="suffix"></div>
          </div>

          <div class="field ">
            <div class="label">Գումարը</div>
            <div class="value w-amt"><span>{{ $amount }}</span></div>
            <div class="suffix">դրամ</div>
          </div>

          <div class="meta ">
            <div class="field ">
              <div class="label">Ամսաթիվ</div>
              <div class="value w-amt"><span>{{ $paid_date }}</span></div>
              <div class="suffix"></div>
            </div>

            <div class="field ">
              <div class="label">Ժամ</div>
              <div class="value w-amt"><span>{{ $paid_time }}</span></div>
              <div class="suffix"></div>
            </div>

            <div class="field ">
              <div class="label">Վճարման տարբերակ</div>
              <div class="value w-amt"><span>{{ strtoupper($paymentType) }}</span></div>
              <div class="suffix"></div>
            </div>
          </div>
        </div>
      </section>

      @if ($i === 1)
        <div class="divider"></div>
      @endif
      @endforeach
    </div>
  </div>

<script>
  (function(){
    const url = new URL(window.location.href);
    if (url.searchParams.get('print') === '1') {
      setTimeout(() => window.print(), 200);
    }
  })();
</script>
</body>
</html>
