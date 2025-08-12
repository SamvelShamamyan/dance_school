function validation(errors){
    let error_elements = $("small[class^='error_']");
    $('.error').empty();
    $.each(error_elements, function(i, el){
        $(el).empty();
    })
    for (const [key, value] of Object.entries(errors)) { 
        $(`small.error_${key}`).text(value);
    }
}


 
async function swal(type, message, reload=false, position = false){
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