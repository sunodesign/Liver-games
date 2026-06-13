<?php
/**
 * Plugin Name: Clover Game Embed
 * Description: [clover_*] ショートコードを /wp-content/uploads/ 内のHTMLファイル内容に置換 (ACFフィールド + 通常本文 + カスタムHTMLブロック + テーマ生出力の4経路対応)
 * Version: 2.0
 * Author: Clover
 *
 * このファイルを /wp-content/mu-plugins/ にアップロードすると自動有効化されます
 *
 * 対応ショートコード:
 *  - [clover_roulette] → ルーレットゲーム
 *  - [clover_gacha]    → ガチャゲーム
 *  - [clover_schedule] → 週間スケジュールメーカー
 *  - [clover_bingo]    → ビンゴ
 *  - [clover_quad]     → X 4分割画像メーカー
 *  - [clover_events]   → GridSnap (縦長4枚画像メーカー)
 *  - [clover_onigiri]  → おにぎりチャレンジ
 *  - [clover_iconring] → OshiRing (アイコンリング合成)
 *  - [clover_panel]    → Clover Panel Break (パネルゲーム)
 *  - [clover_highlow]  → ハイ&ロー (トランプ)
 *
 * v2.0: テーマがメタボックス値などを get_post_meta() で「生のまま echo」
 *       している箇所([clover_*] がそのまま文字表示される)に対応。
 *       フロント表示時にページHTML全体を最後にスキャンし、残っている
 *       [clover_*] をファイル内容へ置換する保険処理(出力バッファ)を追加。
 *       これによりテーマ修正なしで、メタボックス欄に [clover_highlow] と
 *       書くだけでゲームが表示される。
 * v1.9: 二重ロード時の致命的エラー(Cannot redeclare)を修正。
 *       PHPはトップレベルの名前付き関数をコンパイル時に登録するため、
 *       v1.7/1.8 の「function_exists なら return」ガードでは
 *       関数の二重定義を防げず、同フォルダに旧版が残っていると
 *       ホワイトスクリーン(サイトにアクセス不能)になっていた。
 *       関数定義を if(!function_exists()) ブロックで囲み、
 *       フック登録は defined() ガードで1回のみ実行するよう変更。
 * v1.8: ハイ&ロー [clover_highlow] 追加
 * v1.7: 関数名衝突防止 (function_exists ガード)
 * v1.6: render_block フィルタ追加
 * v1.5: add_shortcode 登録経路追加
 */

if (!defined('ABSPATH')) exit;

/**
 * ショートコード → /wp-content/uploads/ のファイル名マッピング
 *
 * ※ 名前付き関数はコンパイル時に登録されるため、必ず
 *    if (!function_exists()) で囲んで二重定義を防ぐ。
 */
if (!function_exists('clover_game_map')) {
    function clover_game_map() {
        return [
            'clover_roulette' => 'clover-roulette.html',
            'clover_gacha'    => 'clover-gacha.html',
            'clover_schedule' => 'clover-schedule.html',
            'clover_bingo'    => 'clover-bingo.html',
            'clover_quad'     => 'clover-quad.html',
            'clover_events'   => 'clover-events.html',
            'clover_onigiri'  => 'clover-onigiri.html',
            'clover_iconring' => 'clover-iconring.html',
            'clover_panel'    => 'clover-panel.html',
            'clover_highlow'  => 'clover-highlow.html',
        ];
    }
}

/**
 * 共通: ファイル内容を返す(無ければ空文字)
 */
if (!function_exists('clover_game_get_file')) {
    function clover_game_get_file($filename) {
        $file = WP_CONTENT_DIR . '/uploads/' . $filename;
        if (file_exists($file)) {
            return file_get_contents($file);
        }
        return '';
    }
}

/**
 * フック登録は1回のみ(二重ロードでも重複登録しない)
 */
if (!defined('CLOVER_GAME_EMBED_LOADED')) {
    define('CLOVER_GAME_EMBED_LOADED', true);

    /**
     * (1) ショートコード登録 — 通常本文・固定ページ・ショートコードブロックで動作
     */
    add_action('init', function () {
        foreach (clover_game_map() as $tag => $filename) {
            add_shortcode($tag, function () use ($filename) {
                return clover_game_get_file($filename);
            });
        }
    });

    /**
     * (2) ACF フィールド内のショートコードも展開
     */
    add_filter('acf/format_value', function ($value, $post_id, $field) {
        if (!is_string($value)) return $value;
        foreach (clover_game_map() as $tag => $filename) {
            $needle = '[' . $tag . ']';
            if (strpos($value, $needle) !== false) {
                $value = str_replace($needle, clover_game_get_file($filename), $value);
            }
        }
        return $value;
    }, 20, 3);

    /**
     * (3) Custom HTML ブロック内でもショートコードを評価
     */
    add_filter('render_block', function ($block_content, $block) {
        if (!isset($block['blockName'])) return $block_content;
        if ($block['blockName'] === 'core/html' && strpos($block_content, '[clover_') !== false) {
            return do_shortcode($block_content);
        }
        return $block_content;
    }, 10, 2);

    /**
     * (4) テーマが get_post_meta() 等で [clover_*] を「生のまま echo」している
     *     箇所(メタボックス値など)への保険。
     *     フロント表示のページHTML全体を最後にスキャンし、未処理で残っている
     *     [clover_*] をファイル内容へ置換する。テーマ修正もメタキーも不要。
     *
     *     ※ ブロック/ショートコード/ACF 経由(1〜3)で既に置換済みの箇所には
     *       [clover_*] は残っていないため二重展開は起きない。
     *       管理画面・REST・AJAX では template_redirect が走らないので影響なし。
     */
    add_action('template_redirect', function () {
        if (is_admin()) return;

        ob_start(function ($html) {
            if (!is_string($html) || strpos($html, '[clover_') === false) {
                return $html;
            }
            foreach (clover_game_map() as $tag => $filename) {
                $needle = '[' . $tag . ']';
                if (strpos($html, $needle) !== false) {
                    $html = str_replace($needle, clover_game_get_file($filename), $html);
                }
            }
            return $html;
        });
    });
}
