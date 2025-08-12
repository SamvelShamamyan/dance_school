<!DOCTYPE html>
<html lang="hy">
<head>
    <meta charset="UTF-8">
    <title>Գրանցում համակարգում</title>
</head>
<body>
    <h2>Բարև, {{ $user->first_name }} {{ $user->last_name }}!</h2>
    <p>Դուք հաջողությամբ գրանցվել եք համակարգում։</p>

    <p><strong>Մուտքի տվյալներ՝</strong></p>
    <ul>
        <li>Էլ․ հասցե (լոգին): {{ $user->email }}</li>
        <li>Գաղտնաբառ: {{ $password }}</li>
    </ul>

    <p>Խնդրում ենք փոխել գաղտնաբառը մուտք գործելուց հետո։</p>
</body>
</html>
