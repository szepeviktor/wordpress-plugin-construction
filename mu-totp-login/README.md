# TOTP login

Log in with your username and a TOTP without your password.

### Enable TOTP for existing users by WP-CLI

```bash
#SECRET_CODE="$(apg -m 32 -x 32 -n 1 -a 1 -M NC -E 0189)"; echo "key=${SECRET_CODE}"
#wp user meta update USER-ID _totp_login_secret_code "$SECRET_CODE"
wp totp add USER-ID
```
