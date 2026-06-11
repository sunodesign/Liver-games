<?php
/**
 * Plugin Name: Clover Game Embed
 * Description: [clover_*] ショートコードを /wp-content/uploads/ 内のHTMLファイル内容に置換 (ACFフィールド + 通常本文 + カスタムHTMLブロックの3経路対応)
 * Version: 1.8
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
 * v1.8: ハイ&ロー [clover_highlow] 追加
 * v1.7: 関数名衝突防止 (function_exists ガード)
 * v1.6: render_block フィルタ追加
 * v1.5: add_shortcode 登録経路追加
 */

if (!defined('ABSPATH')) exit;

// ===== 二重ロード対策: 既に定義済みなら即終了 =====
if (function_exists('clover_game_map')) return;

/**
 * ショートコード → /wp-content/uploads/ のファイル名マッピング
 */
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

/**
 * 共通: ファイル内容を返す(無ければ空文字)
 */
function clover_game_get_file($filename) {
    $file = WP_CONTENT_DIR . '/uploads/' . $filename;
    if (file_exists($file)) {
        return file_get_contents($file);
    }
    return '';
}

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
