function saveOtherOffer() {
    const form = document.getElementById("otherOfferForm");
    const formData = new FormData(form);
    const url = form.getAttribute('action'); 
    $.ajax({        
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        url:  url,
        data: formData,
        cache: false,
        contentType: false,
        processData: false,
        type: 'POST',
        dataType: 'json',
        success: async function(response) {
            if (response.status === 1) {
                await swal("success", "Գործողությունը կատարված է", true, true);
                $('#otherOfferForm')[0].reset();
                showFieldErrors(form, {});
                $('#otherOfferForm').prop('disabled', true);
                // window.location.href = response.redirect;
            }
            if (response.status === 2) {
                  showInfo("info", "", response.message, () => {
                  location.reload();
              });
            }
        },
        error: function(xhr) {
            if (xhr.status === 422) {
                let errors = xhr.responseJSON.errors;
                $.each(errors, function(field, messages) {
                    $(`.error_${field}`).text(messages[0])
                });
            } else {
                swal("error", "Something went wrong!", true, true)
            }
        }
    });
}

$(document).on('submit', '#otherOfferForm', function(e) {
    e.preventDefault();
    saveOtherOffer();
});



$(document).on('change', '#school_id', function () {
  
    const schoolId = $(this).val();

    $.ajax({
      headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
      url: `/admin/otherOffers/getGroupsBySchool/${schoolId}`,
      cache: false,
      type: 'GET',
      dataType: 'json',
      success: function (response) {
            
        let $select = $('#group_ids');
        $select.empty().append('<option></option>');

        if (!response || response.length === 0) {
            $select.prop('disabled', true);

            $select.select2({
                theme: 'bootstrap4',
                placeholder: "Տվյալներ չկան",
                width: '100%'
            });

            return;
        }

        $.each(response, function (index, group) {
            $select.append(
                $('<option>', {
                    value: group.id,
                    text: group.name
                })
            );
        });

        $select.prop('disabled', false);

        $select.select2({
            theme: 'bootstrap4',
            placeholder: "Ընտրել",
            width: '100%'
        });

        if (window.otherOfferEdit && window.otherOfferEdit.isEdit) {
          $select.val(window.otherOfferEdit.selectedGroupIds).trigger('change');
        }

      },
      error: function (xhr) {
        Swal.fire("Սխալ", "Խնդրում ենք կրկին փորձել։", "error");
        console.error(xhr.responseJSON);
      }
   });

});



$(document).on('click', '.js-save-group', function () {
  const $btn = $(this);

  const groupId = $btn.data('group-id');
  const otherOfferGroupId = $btn.data('other-offer-group-id');
  const collapseSelector = $btn.data('collapse');
  const $scope = $(collapseSelector);

  const paid = {};

  $scope.find('input[type="checkbox"][name^="paid["]').each(function () {
    const name = $(this).attr('name'); // paid[57]
    const match = name.match(/^paid\[(\d+)\]$/);

    if (match) {
      const studentId = match[1];
      paid[studentId] = $(this).is(':checked') ? 1 : 0;
    }
  });

  $btn.prop('disabled', true);

  $.ajax({
    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
    url: '/admin/otherOffers/paid',
    type: 'POST',
    dataType: 'json',
    data: {
      group_id: groupId,
      other_offer_group_id: otherOfferGroupId,
      paid: paid
    },

    success: function (res) {
      if (res.status == 1) {
        swal("success", res.message, true, true, function () {
          $btn.prop('disabled', false);
        });
      } else {
        Swal.fire("Սխալ", "Խնդրում ենք կրկին փորձել։", "error",  function () {
          $btn.prop('disabled', false);
        });
      }
    },
    error: function (xhr) {    
      Swal.fire("Սխալ", "Խնդրում ենք կրկին փորձել։", "error",  function () {
        $btn.prop('disabled', false);
      });
    }
  });
});


$(function () {
  if (window.otherOfferEdit && window.otherOfferEdit.isEdit && window.otherOfferEdit.schoolId) {
    $('#school_id').val(window.otherOfferEdit.schoolId).trigger('change');
  }
});