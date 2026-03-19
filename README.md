# ikeda-attendance

Laravel 8 を用いて構築した勤怠管理アプリケーションです。\
本 README.md は **本システムの手引書** として、

- システム概要（何をするシステムか）
- 設計の考え方（データ構造・主要処理）
- 環境構築手順（Docker）
- 起動・動作確認・テスト実行

までを **この README.md だけで完結** できるようにまとめています。

---

## 1. システム概要

本システムは、一般ユーザーの勤怠（出勤・退勤・休憩）を記録し、  
必要に応じて勤怠修正申請を行い、管理者がその申請を確認・承認するための  
勤怠管理システムです。

### 主な機能

- 会員登録 / ログイン（Laravel Fortify）
- メール認証
- 出勤 / 退勤 / 休憩打刻
- 勤怠一覧表示
- 勤怠詳細表示
- 勤怠修正申請
- 修正申請一覧表示
- 管理者によるスタッフ一覧表示
- 管理者による勤怠一覧 / 勤怠詳細確認
- 管理者による修正申請承認
- CSV出力

一般ユーザーは日々の勤怠を打刻し、  
過去の勤怠に誤りがある場合は修正申請を行います。  
管理者は申請内容を確認し、承認した場合のみ勤怠データへ反映されます。

---

## 2. 技術構成

- Framework : Laravel 8
- PHP : 8.1
- 認証 : Laravel Fortify
- DB : MySQL
- Web : nginx / php-fpm
- 環境 : Docker / docker compose
- テスト : PHPUnit

---

## 3. ディレクトリ構成（概要）

    ikeda-attendance/
    ├─ docker compose.yml
    ├─ Dockerfile
    ├─ README.md
    ├─ ER図.svg
    ├─ src/
    │  ├─ app/
    │  ├─ database/
    │  ├─ resources/
    │  ├─ routes/
    │  ├─ tests/
    │  └─ ...

---

## 4. ER図

データ構造を示す ER 図は README.md と同一ディレクトリに配置しています。

[ER図はこちら](./ER図.svg)

---

## 5. データ設計（migration の考え方）

本システムは以下のテーブルを中核として構成されています。

- **users**\
  ユーザー情報を管理します。  
  一般ユーザーと管理者を同一テーブルで管理し、`role` により権限を判別します。

- **attendances**\
  勤怠情報を管理します。  
  1ユーザーにつき1日1レコードを基本とし、出勤時刻・退勤時刻・勤務ステータスを保持します。

- **attendance_breaks**\
  休憩情報を管理します。  
  1つの勤怠に対して複数の休憩を持てる構成にしており、休憩開始・休憩終了を個別に保持します。

- **attendance_correction_requests**\
  勤怠修正申請を管理します。  
  ユーザーが申請した修正内容、備考、承認状態（pending / approved）を保持します。

- **attendance_correction_breaks**\
  修正申請時の休憩情報を管理します。  
  通常の休憩データを直接上書きせず、申請時点の修正内容を別テーブルで保持するために使用します。

詳細なカラム定義は migration ファイルおよび ER 図を参照してください。

---

## 6. 勤怠修正処理の設計方針（重要）

本システムの勤怠修正は、**一般ユーザーが直接勤怠を上書きする方式ではなく、申請 → 管理者承認で確定する方式**を採用しています。

### 一般ユーザー側

- 勤怠詳細画面から修正内容を入力する
- 備考を必須入力とする
- 修正内容は `attendance_correction_requests` に保存する
- ステータスは `pending` で登録する

### 管理者側

- 修正申請一覧から申請を確認する
- 承認時に、申請内容を実際の勤怠データへ反映する
- 反映後、申請のステータスを `approved` に更新する

### この設計にしている理由

- 元の勤怠データを即時変更しないため、履歴管理がしやすい
- 承認フローを挟むことで、運用上の整合性を保てる
- 修正申請時の休憩内容も別保持するため、通常データと申請データを分離できる

また、**承認待ちの申請が存在する場合は新規申請を受け付けない** ようにしています。

---

## 7. 環境構築手順

### 7.1 リポジトリの取得

まず、対象リポジトリを取得し、プロジェクトディレクトリへ移動してください。

```bash
git clone <repository-url>
cd ikeda-attendance
```

---

### 7.2 Docker コンテナ起動

Docker コンテナをビルドして起動します。

```bash
docker compose up -d --build
```

コンテナが正常に起動しない場合は、以下を実行して再ビルドしてください。

```bash
docker compose down
docker compose up -d --build
```

---

### 7.3 Laravel パッケージのインストール

PHP コンテナ内で Laravel の依存パッケージをインストールします。

```bash
docker compose exec php composer install
```

※ 初回起動時は数分かかる場合があります。

---

### 7.4 .env ファイル作成

Laravel 用の環境設定ファイルを作成します。

```bash
docker compose exec php cp .env.example .env
```

---

### 7.5 DB 設定（Docker MySQL）

`.env` に以下を設定してください。

```ini
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel_db
DB_USERNAME=laravel_user
DB_PASSWORD=laravel_pass
```

本システムは Docker 上の MySQL コンテナを利用するため、  
`DB_HOST=mysql` としています。

---

### 7.6 メール設定（MailHog）

本システムではメール認証確認のため MailHog を使用します。  
`.env` に以下を設定してください。

```ini
MAIL_MAILER=smtp
MAIL_HOST=mailhog
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS=test@coachtech.local
MAIL_FROM_NAME="COACHTECH"
```

---

### 7.7 アプリケーションキー生成

Laravel のアプリケーションキーを生成します。

```bash
docker compose exec php php artisan key:generate
```

---

### 7.8 権限エラー対策

環境によっては `storage` や `bootstrap/cache` の権限不足により、  
ログ出力やキャッシュ書き込みでエラーになる場合があります。

以下のようなエラーが表示された場合：

    The stream or file "/var/www/storage/logs/laravel.log" could not be opened
    Permission denied

次を実行してください。

```bash
docker compose exec php chmod -R 777 storage
docker compose exec php chmod -R 777 bootstrap/cache
```

---

### 7.9 マイグレーション・シーディング

テーブル作成および初期データ投入を行います。

```bash
docker compose exec php php artisan migrate --seed
```

必要なユーザー・勤怠関連データがあらかじめ投入される想定です。  
詳細は seeder / factory を参照してください。

---

### 7.10 Mail 設定反映

MailHog の設定を確実に反映するため、PHP コンテナを再起動します。

```bash
docker compose restart php
```

---

## 8. アプリケーション起動確認

ブラウザで以下にアクセスしてください。

    http://localhost

正常に起動していれば、トップページから会員登録画面へ遷移します。

---

## 9. メール確認（MailHog）

メール認証や認証メール再送の確認には MailHog を使用します。

ブラウザで以下へアクセスしてください。

    http://localhost:8025

ここで送信されたメールを確認できます。

---

## 10. テスト環境構築およびテスト実施手順

本システムでは、本番環境とは別に **テスト専用データベース** を使用して  
Feature Test を実行します。

---

### 10.1 テスト用データベース作成

MySQL コンテナに入り、テスト用データベースを作成してください。

```bash
docker compose exec mysql bash
mysql -u root -p
```

MySQL に入ったら、以下を実行してください。

```sql
CREATE DATABASE laravel_db_testing
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;
```

終了します。

```bash
exit
exit
```

---

### 10.2 .env.testing 作成

PHP コンテナに入り、`.env.testing` を作成して初期化します。

```bash
docker compose exec php bash
cp .env .env.testing
php artisan key:generate --env=testing
php artisan migrate --env=testing
php artisan config:clear
php artisan cache:clear
exit
```

---

### 10.3 .env.testing 設定

`.env.testing` に以下を設定してください。

```ini
APP_ENV=testing

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel_db_testing
DB_USERNAME=root
DB_PASSWORD=root

MAIL_MAILER=smtp
MAIL_HOST=mailhog
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS=test@coachtech.local
MAIL_FROM_NAME="COACHTECH"
```

---

### 10.4 テスト実行

```bash
docker compose exec php php artisan test
```

実行結果：

- **38 tests passed**

すべてのテストが成功していることを確認済みです。

---

## 11. バリデーション方針

本システムでは、すべての主要入力について **FormRequest** を使用しています。

- 会員登録
- ログイン
- 管理者ログイン
- 勤怠修正申請
- 管理者側勤怠更新

また、`form` タグには `novalidate` を付与し、  
ブラウザ標準バリデーションではなく Laravel 側でエラーメッセージを表示する構成としています。

勤怠修正・管理者更新では、以下のような業務ルールもバリデーションで担保しています。

- 出勤時間は退勤時間より前であること
- 休憩時間は勤務時間内であること
- 休憩終了は休憩開始より後であること
- 備考は必須であること

---

## 12. 補足事項

- 出勤は 1 日 1 回のみ可能です
- 勤怠は日単位で管理しています
- 修正申請は承認後に勤怠へ反映されます
- 承認待ちの申請が存在する場合、再申請はできません
- メール認証未完了の場合、一部機能は利用できません
- 画像アップロード機能は実装していないため、`php artisan storage:link` は不要です
- ヘッダーロゴは `public/image` 配下の画像を利用しています

---

## 13. URL

- トップページ  
  http://localhost/

- ログインページ  
  http://localhost/login

- 会員登録ページ  
  http://localhost/register

- 管理者ログインページ  
  http://localhost/admin/login

- MailHog  
  http://localhost:8025

---

以上の手順に従うことで、本システムを構築・起動・動作確認できます。