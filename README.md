# Liver Games — Clover Game Embed

`[clover_*]` ショートコードを `/wp-content/uploads/` 内のHTMLファイルの内容に
置換して埋め込む must-use プラグインです。ACFフィールド・通常本文・カスタムHTML
ブロックの3経路に対応しています。

## 構成

| ファイル | 配置先 |
| --- | --- |
| `mu-plugins/clovergame.php` | `/wp-content/mu-plugins/clovergame.php` |
| `uploads/clover-*.html` | `/wp-content/uploads/clover-*.html` |

> `mu-plugins` に置いたプラグインは有効化操作なしで自動的に読み込まれます。

## 対応ショートコード

| ショートコード | ファイル | 内容 |
| --- | --- | --- |
| `[clover_roulette]` | `clover-roulette.html` | ルーレットゲーム |
| `[clover_gacha]` | `clover-gacha.html` | ガチャゲーム |
| `[clover_schedule]` | `clover-schedule.html` | 週間スケジュールメーカー |
| `[clover_bingo]` | `clover-bingo.html` | ビンゴ |
| `[clover_quad]` | `clover-quad.html` | X 4分割画像メーカー |
| `[clover_events]` | `clover-events.html` | GridSnap (縦長4枚画像メーカー) |
| `[clover_onigiri]` | `clover-onigiri.html` | おにぎりチャレンジ |
| `[clover_iconring]` | `clover-iconring.html` | OshiRing (アイコンリング合成) |
| `[clover_panel]` | `clover-panel.html` | Clover Panel Break (パネルゲーム) |
| **`[clover_highlow]`** | **`clover-highlow.html`** | **ハイ&ロー (トランプ) ← 今回追加** |

## 導入手順（ハイ&ロー）

1. `uploads/clover-highlow.html` をサーバーの `wp-content/uploads/clover-highlow.html` にアップロード。
2. `mu-plugins/clovergame.php` をサーバーの `wp-content/mu-plugins/clovergame.php` に配置（v1.8）。
3. ACFの項目・投稿本文・カスタムHTMLブロックのいずれかに記述:

   ```
   [clover_highlow]
   ```

## ゲームの遊び方（ハイ&ロー）

- 中央のカードに対して、次のカードが「**ハイ（上）**」か「**ロー（下）**」かを予想します。
- A(1) が最弱、K(13) が最強です。
- 当たれば連勝が伸び、外れるとゲームオーバー。同じ数字なら引き分けで続行します。

## 実装メモ

- ゲームHTMLは**インライン埋め込み用のフラグメント**です（`<!DOCTYPE>`/`<html>` を持たない）。
  CSS・JS はすべて `#clover-highlow` 配下にスコープしてあり、テーマや他のゲームと
  干渉しません。JSは多重初期化を防ぐためのガード付きです。
