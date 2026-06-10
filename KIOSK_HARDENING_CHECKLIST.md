# Kiosk Hardening Checklist (Golden Image)

This checklist is for creating a runtime-only kiosk image for stand machines.
Use TheMan as your admin/build machine and do NOT clone TheMan directly as runtime.

## Goal

Create a machine image where staff can only use the kiosk app and cannot access development tools or system controls.

## 1) Machine Roles

- TheMan (admin/build): keeps VS Code, Copilot, Git, SSH keys, and dev tooling.
- Kiosk runtime image: no VS Code, no dev keys, no admin access for staff.

## 2) Accounts and Permissions

- Create a dedicated runtime user (example: kiosk).
- Do not add runtime user to sudo group.
- Set a strong local admin password known only to management/IT.
- Disable guest login.

Commands:

```bash
sudo adduser kiosk
sudo deluser kiosk sudo || true
id kiosk
```

## 3) Remove Developer Surface on Runtime Image

Run on runtime image only (not TheMan):

- Remove VS Code and developer IDEs.
- Remove Git credentials and SSH private keys from runtime users.
- Remove shell shortcuts to terminal for kiosk user.

Examples:

```bash
# Optional package removal (adjust to your distro)
sudo snap remove code || true
sudo apt remove -y code git gh || true

# Remove SSH keys for runtime user
sudo -u kiosk rm -f /home/kiosk/.ssh/id_* || true
```

## 4) Auto-Start Kiosk Services

Ensure app and browser services are enabled for kiosk operation.

Your repo provides:
- blastoff/kiosk-laravel.service
- blastoff/kiosk-browser.service

Enable for the kiosk user session (or system user mode as needed):

```bash
systemctl --user daemon-reload
systemctl --user enable --now kiosk-laravel.service kiosk-browser.service
```

## 5) Lock Down Session Access

- Auto-login to kiosk user.
- Disable screen lock and power suspend interruptions.
- Prevent access to settings panels where possible.
- Disable virtual console switching if physically exposed keyboards are present.

Common controls to review:
- Ctrl+Alt+T terminal shortcut
- Ctrl+Alt+F1..F6 console switch
- Alt+F4 close app (if not desired)

## 6) Network and Remote Admin

- Keep SSH enabled for admin support.
- Use SSH keys only; disable password SSH login.
- Restrict SSH source IPs with firewall where possible.

Example `/etc/ssh/sshd_config` hardening:

```text
PasswordAuthentication no
PermitRootLogin no
PubkeyAuthentication yes
```

Restart ssh service after changes.

## 7) Data and App State

- Confirm video files and DB are present.
- Confirm barcode counts before imaging.
- Confirm scanner input works on idle screen.

## 8) TheMan-Only Replication

Nightly replication should run only on TheMan:
- Uses NODE_ROLE=man or MAN_HOSTNAME match.
- Client machines should not run replication timer.

Files:
- blastoff/sync_videos_to_nodes.sh
- blastoff/kiosk-video-replication.service
- blastoff/kiosk-video-replication.timer

## 9) Pre-Image Final Validation

- Kiosk app starts after reboot.
- Barcode scan works.
- No visible VS Code/dev tools for staff.
- Runtime user is not sudo.
- SSH admin access works with key.

Run helper check:

```bash
bash blastoff/post_clone_safety_check.sh
```

## 10) Clone Rollout

- Capture image from validated runtime machine.
- Clone to target machines.
- On each clone, set unique:
  - hostname
  - IP/network config
  - any machine-specific IDs

## 11) Post-Clone Acceptance Test (Per Machine)

- Boot and auto-open kiosk.
- Scan 2 to 3 known UPC products.
- Confirm idle loop resumes.
- Confirm machine is locked down.

