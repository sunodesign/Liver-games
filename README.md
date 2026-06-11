# Liver Games — ハイ&ロー（High & Low）

トランプの「ハイ&ロー」ゲームを WordPress のショートコードで埋め込めるようにしたものです。
ACF（Advanced Custom Fields）の項目にショートコードを記述して呼び出すこともできます。

## 構成

| ファイル | 配置先 | 役割 |
| --- | --- | --- |
| `uploads/highlow.html` | `/wp-content/uploads/highlow.html` | ゲーム本体（単体で動作する HTML/CSS/JS） |
| `mu-plugins/clover-game.php` | `/wp-content/mu-plugins/clover-game.php` | ショートコードを登録する must-use プラグイン |

> `mu-plugins`（must-use plugins）に置いたプラグインは、有効化操作なしで自動的に読み込まれます。

## 導入手順

1. `uploads/highlow.html` を、サーバーの `wp-content/uploads/highlow.html` にアップロードします。
2. `mu-plugins/clover-game.php` を、サーバーの `wp-content/mu-plugins/clover-game.php` に配置します。
   （`mu-plugins` ディレクトリが無ければ作成してください）
3. 投稿・固定ページ・ウィジェット、または ACF の項目に次のショートコードを記述します。

   ```
   [highlow]
   ```

## ショートコードのオプション

| 属性 | 既定値 | 説明 |
| --- | --- | --- |
| `height` | `620` | 高さ（数値のみなら px）。例: `height="640"` |
| `width` | `100%` | 幅。例: `width="480"` または `width="100%"` |
| `src` | uploads の `highlow.html` | 読み込む HTML ファイルの URL |
| `title` | `ハイ&ロー ゲーム` | iframe のタイトル（アクセシビリティ用） |

例:

```
[highlow height="640" width="100%"]
```

別名タグ `[clover_game]` も同じように使えます。

## ACF の項目で使う

ACF の **テキスト** / **テキストエリア** 項目に書いたショートコードは、
本プラグインが自動的に `do_shortcode()` で展開します。
**WYSIWYG（ワープロ）** 項目は WordPress 標準でショートコードが展開されます。

テンプレート側で値を出力する例:

```php
<?php echo get_field( 'game_area' ); // [highlow] が展開されて表示される ?>
```

> テンプレートで `the_field()` / `get_field()` を使う場合、フィルター
> `acf/format_value` が適用されるため、ショートコードが展開されます。
> `esc_html()` などでエスケープして出力すると展開されないのでご注意ください。

## ゲームの遊び方

- 中央のカードに対して、次のカードが「**ハイ（上）**」か「**ロー（下）**」かを予想します。
- A(1) が最弱、K(13) が最強です。
- 当たれば連勝が伸び、外れるとゲームオーバー。同じ数字なら引き分けで続行します。
