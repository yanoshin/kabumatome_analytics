kabumatome_analytics
====================

市況かぶ全力2階建さんをゴニョゴニョ

Requirements

This depends on PHP 5.4+ and Guzzle 4+


curl -sS https://getcomposer.org/installer | php
./composer.phar update



一気に処理して全力2階建さんへ負荷をかけないよう、HTMLリクエストには毎回1秒のウェイトをかけています。（だからちょっと時間かかるので、待っててね）