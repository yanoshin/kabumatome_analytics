市況かぶ全力2階建さん 登場者ランキング2015
====================

【Update!]2015年に対応しました。（2015-11-25)


Requirements

-  This depends on PHP 5.4+ and Guzzle 4+

# Description

ブログサイト「[市況かぶ全力２階建](http://kabumatome.doorblog.jp/)」さんの2015年中の記事に引用登場したTwitterアカウントのランキングを集計します。

こちらのブログ記事もご覧ください。

- （昨年版）[市況かぶ全力２階建さんの人気ランキング2014 作ってみた、ソースコードも公開してみた。 | YANOSHIN blog](http://blog.bresson.biz/2014/12/kabumatome_ranking_2014_in_progress.html)
- [http://blog.bresson.biz/2014/12/kabumatome_ranking_2014_in_progress.html](http://blog.bresson.biz/2014/12/kabumatome_ranking_2014_in_progress.html)



## Rule

- ２階建さんの2015年中の公開記事を対象にしています。（期間はconfig.phpにて定義）
- １記事につき何回引用されていても登場１回とカウントしてます。
- 全力２階建 @kabumatome さんのアカウントだけはランキング除外としました。
- 一気に処理して全力2階建さんへ負荷をかけないよう、HTMLリクエストには毎回1秒のウェイトをかけています。（だからちょっと時間かかるので、待っててね）


# SETUP

ソースコードの取得
```
$ git clone https://github.com/yanoshin/kabumatome_analytics.git
$ cd kabumatome_analytics/
```

HTMLパーサのGoutteなど必要なライブラリをcomposerにて導入します。
```
$ curl -sS https://getcomposer.org/installer | php
$ ./composer.phar update
```


# How to use

次のコマンドを実行すると、集計が始まります。
```
$ php hack_kabumatome.php
```

以下は実行結果の表示例
```
〜処理が始まります

記事一覧を取得中：http://kabumatome.doorblog.jp/?p=1
記事一覧を取得中：http://kabumatome.doorblog.jp/?p=2
記事一覧を取得中：http://kabumatome.doorblog.jp/?p=3

(略）
〜集計結果が表示されます

——————
★ 結果発表 ★
——————
全 532 記事中からの登場ユニーク数ランキング2015
(略）
```

