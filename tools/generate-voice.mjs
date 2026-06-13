#!/usr/bin/env node
/**
 * Clover High & Low — ElevenLabs 音声クリップ生成スクリプト
 *
 * ハイ&ローで使う固定セリフ(数字13種 + 勝敗3種 = 16個)を ElevenLabs で
 * 一括生成し、mp3 として書き出します。書き出した mp3 を WordPress の
 *   /wp-content/uploads/highlow-voice/
 * にアップロードすると、ゲームが端末TTSの代わりにその音声を再生します。
 *
 * 使い方:
 *   1) ElevenLabs でAPIキーと、使いたい声(Voice)のIDを用意
 *   2) 実行(APIキーは環境変数で渡す。チャット等に貼らないこと):
 *        ELEVENLABS_API_KEY=あなたのキー node tools/generate-voice.mjs <voice_id>
 *      出力先を変える場合:
 *        ELEVENLABS_API_KEY=... node tools/generate-voice.mjs <voice_id> ./out
 *   3) 生成された mp3 を /wp-content/uploads/highlow-voice/ にアップロード
 *
 * 必要環境: Node 18+ (グローバル fetch を使用)
 */
import fs from "fs";
import path from "path";

const API_KEY  = process.env.ELEVENLABS_API_KEY;
const VOICE_ID = process.argv[2];
const OUT_DIR  = process.argv[3] || "uploads/highlow-voice";
const MODEL_ID = process.env.ELEVENLABS_MODEL || "eleven_multilingual_v2";

if (!API_KEY) {
  console.error("✗ 環境変数 ELEVENLABS_API_KEY が未設定です。");
  process.exit(1);
}
if (!VOICE_ID) {
  console.error("✗ Voice ID を指定してください: node tools/generate-voice.mjs <voice_id>");
  process.exit(1);
}

// ファイル名(.mp3) → 読み上げるテキスト
// ※ ゲーム側 CLIP マップのファイル名と一致させること
const PHRASES = {
  "rank-a":  "エース",
  "rank-2":  "に",
  "rank-3":  "さん",
  "rank-4":  "よん",
  "rank-5":  "ご",
  "rank-6":  "ろく",
  "rank-7":  "なな",
  "rank-8":  "はち",
  "rank-9":  "きゅう",
  "rank-10": "じゅう",
  "rank-j":  "ジャック",
  "rank-q":  "クイーン",
  "rank-k":  "キング",
  "win":     "せいかい！",
  "lose":    "ざんねん！",
  "draw":    "ひきわけ！"
};

// 声の質感(お好みで調整)。style を上げると抑揚が強め=可愛い寄り
const VOICE_SETTINGS = {
  stability: 0.40,
  similarity_boost: 0.80,
  style: 0.35,
  use_speaker_boost: true
};

fs.mkdirSync(OUT_DIR, { recursive: true });

let ok = 0, ng = 0;
for (const [name, text] of Object.entries(PHRASES)) {
  try {
    const res = await fetch(`https://api.elevenlabs.io/v1/text-to-speech/${VOICE_ID}`, {
      method: "POST",
      headers: {
        "xi-api-key": API_KEY,
        "accept": "audio/mpeg",
        "content-type": "application/json"
      },
      body: JSON.stringify({ text, model_id: MODEL_ID, voice_settings: VOICE_SETTINGS })
    });
    if (!res.ok) {
      ng++;
      console.error(`✗ ${name}: HTTP ${res.status} ${await res.text()}`);
      continue;
    }
    const buf = Buffer.from(await res.arrayBuffer());
    const file = path.join(OUT_DIR, `${name}.mp3`);
    fs.writeFileSync(file, buf);
    ok++;
    console.log(`✓ ${file}  「${text}」`);
  } catch (e) {
    ng++;
    console.error(`✗ ${name}: ${e.message}`);
  }
}

console.log(`\n完了: 成功 ${ok} / 失敗 ${ng}`);
console.log(`→ ${OUT_DIR}/ の mp3 を /wp-content/uploads/highlow-voice/ にアップロードしてください。`);
