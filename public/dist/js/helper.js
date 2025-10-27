//Validation
// ---------- helpers ----------
function toErrorClassKey(s) {
  if (!s) return '';
  let key = String(s).replace(/\./g, '_');
  // 2) [] -> '', [x] -> _x
  key = key.replace(/\[\]/g, '');
  key = key.replace(/\[([^\]]+)\]/g, '_$1');
  return key;
}

function elForErrorKey(key) {
  return $(`small.error_${toErrorClassKey(key)}`);
}

function clearAllErrors(scope) {
  const $root = scope ? $(scope) : $(document);
  $root.find("small[class^='error_'], .text-danger").text('');
  $root.find('.is-invalid').removeClass('is-invalid');
}

// ---------- rendering errors ----------
function validation(errors) {
  clearAllErrors();
  if (!errors || typeof errors !== 'object') return;

  Object.entries(errors).forEach(([key, val]) => {
    const msg = Array.isArray(val) ? val[0] : val;
    const $small = elForErrorKey(key);
    if ($small.length) $small.text(msg);
  });
}

function showFieldErrors(formEl, errors) {
  clearAllErrors(formEl);
  if (!errors || typeof errors !== 'object') return;

  $.each(errors, function (field, messages) {
    const msg = Array.isArray(messages) ? messages[0] : messages;
    const $small = elForErrorKey(field);
    if ($small.length) $small.text(msg);
  });
}

// ---------- inline clearing ----------
function attachInlineErrorClearing() {
  $(document).on('input change','#UserForm,#roomForm,#GroupForm,#schoolForm,#StaffForm,#StudentForm,#SutdentGroupModalForm,#StaffGroupModalForm', function (e) {
    const t = e.target;
    const $t = $(t);

    const rawName = t.name || $t.attr('name') || '';
    const key = toErrorClassKey(rawName);

    let hasValue = false;
    if ($t.is('select')) {
      const v = $t.val();
      hasValue = Array.isArray(v) ? v.length > 0 : (v !== null && String(v).trim() !== '');
    } else if ($t.is(':checkbox,:radio')) {
      const $group = $(this).find(`[name="${rawName}"]`);
      hasValue = $group.filter(':checked').length > 0;
    } else {
      const v = $t.val();
      hasValue = v !== null && String(v).trim() !== '';
    }

    if (hasValue && key) {
      const $small = elForErrorKey(key);
      if ($small.length) $small.text('');
      $t.removeClass('is-invalid');
    }
  });
}

$(function () {
  attachInlineErrorClearing();
});

//Validation

 
async function swal(type, message, reload = false, position = false){
    const Toast = Swal.mixin({
        toast: true,
        position: "top-end",
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        didOpen: (toast) => {
          toast.onmouseenter = Swal.stopTimer;
          toast.onmouseleave = Swal.resumeTimer;
        }
      });
    await Toast.fire({
        icon: type,
        title: message
      });
      if(reload && position){
        await location.reload()
      } 
}


function showInfoWithAction(title, text, onContinueUrl, extraData = {}, onSuccess = null) {
    Swal.fire({
        icon: 'info',
        title: title || 'Գործողություն',
        html: text || '',
        showCancelButton: true,
        confirmButtonText: 'Շարունակել',
        cancelButtonText: 'Փակել',
        reverseButtons: false
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: onContinueUrl,
                method: 'POST',
                data: extraData,
                dataType: 'json',
                success: function (res) {
                    if (res.status == 1 && typeof onSuccess === "function") {
                        onSuccess();
                    } else {
                        Swal.fire("Սխալ", res.message || "Սխալ է տեղի ունեցել", "error");
                    }
                },
                error: function () {
                    Swal.fire("Սխալ", "Սերվերի սխալ", "error");
                }
            });
        }
    });
}

function showInfo(type,title,text,callback = null) {
    title = (typeof title !== 'undefined') ? title : 'Գործողությունը';
    text = (typeof text !== 'undefined') ? text : 'Կատարված է';

    if (type === 'success') {
        Swal.fire({
            icon: 'success',
            title: title,
            text: text,
            showConfirmButton: true,
            confirmButtonText: 'Փակել',
            willClose: callback
        });
    }

    if (type === "info") {
        Swal.fire({
            icon: 'info',
            title: title,
            text: text,
            showConfirmButton: true,
            confirmButtonText: 'Փակել',
            willClose: callback
        });
    }

    if (type === "error") {
        Swal.fire({
            icon: 'error',
            title: title,
            text: text,
            showConfirmButton: true,
            confirmButtonText: 'Փակել',
            willClose: callback
        });
    }
}

function clear(){
  $('.clearable').val("");
}

