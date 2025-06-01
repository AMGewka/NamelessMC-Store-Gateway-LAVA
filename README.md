# LAVA Gateway Integration for NamelessMC Store

This module integrates the [LAVA](https://lava.ru) payment system into the NamelessMC Store plugin. It supports redirect-based payments, simple signature verification, and customizable account settings.

---

## 🛡️ Security

- Uses HMAC-SHA256 signature for verification
- Signatures include `shop_id`, `amount`, `order_id` and are built as:
  `sha256(shop_id:amount:secret)`
- Server-side pingback also uses the same signature mechanism
- Optional secret2 for callback validation

---

## ⚙️ Functionality Overview

### ✅ Payment Acceptance

- Redirects users to: `https://api.lava.ru/merchant/pay`
- Required fields:
    - `shop_id`
    - `order_id`
    - `amount`
    - `sign`
    - `currency` (optional)
    - `comment`, `success_url`, `fail_url`
- Callback is handled via `listener.php` with signature check
- Response is verified via POST + `X-Sign` header

### 💰 Balance Check (Optional)

- API endpoint: `https://api.lava.ru/business/get-balance`
- Requires Bearer token (Lava API key)
- Allows displaying actual balance in admin panel

---

## 🌐 Supported Currencies

- RUB (default)
- Can be extended in Lava merchant settings

---

## 🧱 API Architecture

- Payment: redirect with signed fields
- Callback: POST JSON with `X-Sign` HMAC header
- Balance: `Authorization: Bearer <API_KEY>` header

---

## 🧩 NamelessMC Store Compatibility

- Compatible with Store v1.8.3+
- Implements:
    - `GatewayBase`, `Order`, `StoreConfig`, `Language`
    - `curl`, `Session::flash`, `Token::check`
- Admin panel settings: `settings.tpl`
- Language support via `language/*.json`

---

## ⚠️ Limitations

- No subscription or recurring payments
- No sandbox mode (only production merchant keys)
- Callback must be verified using provided `X-Sign` HMAC

---

## 🔗 Documentation

- [LAVA API Documentation](https://dev.lava.ru/)
- [NamelessMC Store Module](https://github.com/partydragen/Nameless-Store)
