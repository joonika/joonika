<?php
$title = 'welcome';
$jsonDecode = \Joonika\helper\Cache::get('themeSampleJson');
if (empty($jsonDecode)) {
    $dirCheck = file_get_contents(__DIR__ . '/../../../composer.json');
    if ($dirCheck) {
        $jsonDecode = json_decode($dirCheck, JSON_UNESCAPED_UNICODE);
        \Joonika\helper\Cache::set('themeSampleJson',$jsonDecode);
    }
}
if (!empty($jsonDecode['description'])) {
    $title = $jsonDecode['description'];
} elseif (!empty($jsonDecode['name'])) {
    $title = $jsonDecode['name'];
}?>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?></title>
    <style>
        .center {
            height: 100px;
            text-align: center;
            position: absolute;
            top: 0;
            bottom: 0;
            left: 0;
            right: 0;
            margin: auto;
        }

        .center .title {
            font-size: 30px;
        }

        .center .description {
            font-size: 25px;
            margin-top: 10px;
            border-top: solid 1px silver;
        }

    </style>
</head>
<body>
<div class="center">
    <div>
        <div class="title"><?= $title ?></div>
        <div class="description">
            <?= JK_HOST() ?>
        </div>
    </div>
</div>
</body>
</html>
