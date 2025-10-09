// let inactivityTime = function () {
//     let timeout;

//     function logoutUser() {
//         $.ajax({
//             url: '/logout',
//             type: 'POST',
//             headers: {
//                 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
//             },
//             complete: function () {
//                 window.location.href = '/'; 
//             }
//         });
//     }

//     function resetTimer() {
//         clearTimeout(timeout);
//         // timeout = setTimeout(logoutUser, 15 * 60 * 1000);
//         timeout = setTimeout(logoutUser, 10 * 1000); // ⏱ 10 sec
//     }

//     window.onload = resetTimer;
//     document.onmousemove = resetTimer;
//     document.onkeypress = resetTimer;
//     document.onclick = resetTimer;
//     document.onscroll = resetTimer;
// };

// $(document).ready(function () {
//     inactivityTime();
// });

// $(function () {
//     $('body').addClass('page-transition');

//     $('a:not([target="_blank"]):not([href^="#"])').on('click', function (e) {
//         const href = $(this).attr('href');
//         if (!href || href.startsWith('javascript:')) return;

//         e.preventDefault();
//         $('#preloader').fadeIn(200);

//         setTimeout(() => {
//             window.location.href = href;
//         }, 300); 
//     });
// });

function formatPhone(phone) {
    if (!phone) return '';
    phone = phone.toString().replace(/\D/g, '');

    if (phone.length < 8) return phone;

    const match = phone.match(/^(\d{3})(\d{2})(\d{2})(\d{2})$/);
    if (match) {
        return `(${match[1]})${match[2]}-${match[3]}-${match[4]}`;
    }

    return phone.replace(/(\d{3})(?=\d)/g, '$1 ');
}
