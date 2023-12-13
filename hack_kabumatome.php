<?php
// Github: https://github.com/yanoshin/kabumatome_analytics

//Special thanks to 市況かぶ全力２階建さん
//http://kabumatome.doorblog.jp/

// Depricatedメッセージ抑制（依存しているライブラリが古いままなので）
error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/models/article.php';

use Goutte\Client;

//setup Goutte
$client = new Client();
$client->getClient()->setDefaultOption('config/curl/' . CURLOPT_TIMEOUT, 60);
// GuzzleHttp\ClientのSSL証明書の検証問題の回避
$client->getClient()->setDefaultOption('config/curl/' . CURLOPT_SSL_VERIFYPEER, false);

/*
 * 記事一覧画面を解析する
 */

/**
 * @var Article[] $articles
 */
$articles = array();

$index = 0;
$keep_fetching = true; //一覧取得を続けるかの判断フラグ

while ($keep_fetching === true) {
    $index++;

    $index_url = sprintf(TMPL_URL_INDEX, $index);
    echo sprintf("記事一覧を取得中：%s \n", $index_url);
    sleep(1); //お行儀よく待ちましょう
    $crawler = $client->request('GET', $index_url);

    $crawler->filter('header.article-header')->each(function ($node) {
        global $articles;
        global $keep_fetching;

        try {
            /**
             * @var Symfony\Component\DomCrawler\Crawler $node
             */
            $article = new Article();
            $article->setUrl($node->filter('h1.article-title a')->attr('href'));
            $article->setTitle($node->filter('h1.article-title')->text());
            $article->setCategory($node->filter('dl.article-category a')->text());
            $article->setPublishDatetime($node->filter('p.article-date time')->attr('datetime'));

        } catch (Exception $e) {
            //たまに"The current node list is empty."が発生することがあるので、その場合は例外を拾ってこちらで処理
            //Goutteよりも単純な方法でHTMLをパースするメソッド simple_html_parse()を用意した。
            $simple_xml = simple_html_parse($node->html());
            $article = new Article();
            $article->setUrl((string)$simple_xml->body->h1->a->attributes()->href);
            $article->setTitle((string)$simple_xml->body->h1->a);
            $article->setCategory((string)$simple_xml->body->dl->dd);
            $article->setPublishDatetime((string)$simple_xml->body->p->time->attributes()->datetime);
        }

        //取得対象時期内ならば
        if ($article->getPublishDatetime() >= DATETIME_START && $article->getPublishDatetime() <= DATETIME_END) {
            //$article->save();
            $articles[] = $article;
        }

        //取得対象時期よりも前にきたら、一覧取得終了
        if ($article->getPublishDatetime() < DATETIME_START) {
            $keep_fetching = false;
        }
    });
}

/*
 * 記事をひとつひとつ拾って、集計する
 */

$unique = array();
$ranking = array();
$link_art = array();
foreach ($articles as $article) {
    sleep(1); //お行儀よく待ちましょう
    $crawler = $client->request('GET', $article->getUrl());

    $crawler->filter('div.article-body-inner')->each(function ($node) {
        /**
         * @var Article $article
         */
        global $article;

        /**
         * 1記事あたり何回出現しても1カウントとするための、ユニーク数生成用
         */
        global $unique;
        $unique = array(); //記事毎にリセット

        echo sprintf("---------\n");
        echo sprintf("%s  ： %s \n", $article->getPublishDatetime('Y-m-d'), $article->getTitle());
        echo sprintf("      URL   ： %s \n", $article->getUrl());
        echo "\n";

        /**
         * @var Symfony\Component\DomCrawler\Crawler $node
         */
        $node->filter('blockquote.twitter-tweet')->each(function ($node_tweet) {
            /**
             * @var array $ranking
             */
            global $ranking;

            global $unique;

            /**
             * @var array $link_art
             */
            global $link_art;

            /**
             * @var Article $article
             */
            global $article;

            /**
             * @var Symfony\Component\DomCrawler\Crawler $node_tweet
             */

            // 2020-12 Twitter IDの取得方法を変えた
            try{
                preg_match('/@(.+)/', $node_tweet->filter('span.nickname')->text(), $matches);
                $twitter_id = $matches[1];
            }catch(Exception $e){
                //TwitterアカウントIDは本文の中に(@hogehoge)とあるだけなので、正規表現でマッチングさせる
                preg_match('/\(@(.+)\)/', $node_tweet->text(), $matches);
                $twitter_id = $matches[1];
            }
            if(is_null($twitter_id)){
                preg_match('/https\:\/\/twitter.com\/(.+)/', $node_tweet->filter('a')->first()->attr('href'), $matches);
                $twitter_id = $matches[1];
            }

            // 全力２階建 @kabumatome さんのアカウントだけはランキング除外とした
            if ($twitter_id == 'kabumatome') {
                return null;
            }

            //すでに記事中に登場しているユーザーはカウントアップしない。（ユニーク数算出に変更）
            if (!in_array($twitter_id, $unique)) {
                $unique[] = $twitter_id;

                //カウントを加算
                if (isset($ranking[$twitter_id]) === false) {
                    $ranking[$twitter_id] = 0; //初カウントなら初期化
                }
                $ranking[$twitter_id]++;
                $link_art[$twitter_id][] = $article->getUrl();
            }

            //echo "\x1b[1A\x1b[K"; //表示行追加せず、１行を更新
            //echo sprintf(" Twitter ID ： %s \n", $twitter_id);
        });
    });
}

/*
 * ランキングを表示する
 */

echo "------------------</br>\n";
echo sprintf("  ★ 結果発表(全%s名） ★</br>\n", number_format(count($ranking)));
echo "------------------</br>\n";

echo sprintf(" 全 %s 記事中からの登場ユニーク数ランキング </br>\n", count($articles));
echo " （＊注意）全力２階建 @kabumatome さんのアカウントだけはランキング除外としました</br>\n";
echo "</br>\n";

$rank = 0;
$prev_count = 0;
$prev_rank = 1;
$current_rank = null;
arsort($ranking); //登場数順に並べ替え
foreach ($ranking as $twitter_id => $count) {
    $rank++;
    if($prev_count==$count) {
        $current_rank = $prev_rank;
    } else {
        $current_rank = $rank;
        $prev_rank = $rank;
    }
    echo sprintf("第 %s 位： <A HREF='https://twitter.com/%s' target='_twitter' rel='nofollow'>@%s</A> さん ( %s回 )\n", number_format($current_rank), $twitter_id, $twitter_id, number_format($count));

    if(isset($link_art[$twitter_id])){
        // 3件以上登場する人は改行を挿入
        if(count($link_art[$twitter_id])>2){
            echo sprintf("</br>&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;\n");
        }

        foreach ($link_art[$twitter_id] as $cnt=>$la){
            if($cnt == 0){
                $caption = sprintf('[登場記事%s]', $cnt+1);
            } else {
                $caption = sprintf('[%s]', $cnt+1);
            }
            echo sprintf("\t&nbsp;<A HREF='%s' target='_twitter' rel='nofollow'>%s</A> ", $la, $caption);
        }
    }
    $prev_count = $count;

    echo sprintf("</br>\n");
}

echo "\n"; //終わり

/**
 * ユーティリティメソッド：簡易HTMLパーサー
 */

function simple_html_parse($html)
{
    $domDocument = new DOMDocument();
    @$domDocument->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', mb_detect_encoding($html))); //なんかXML変換時に文字化けることがあるので
    $xmlString = $domDocument->saveXML();
    $xmlObject = simplexml_load_string($xmlString);
    return $xmlObject;
}