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