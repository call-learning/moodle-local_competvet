
# Apereo CAS Server with Docker for Moodle Authentication Testing

This guide provides step-by-step instructions to set up a Dockerized Apereo CAS server with SSL enabled, facilitating CAS authentication testing with Moodle.

## Prerequisites

- [Docker](https://www.docker.com/products/docker-desktop) installed on your machine.
- OpenSSL installed for generating a self-signed certificate.
- Basic knowledge of Docker commands.

## Step 1: Pull the Apereo CAS Docker Image

Begin by pulling the specific Apereo CAS Docker image from Docker Hub:

```bash
docker pull apereo/cas:v5.3.10
```

## Step 2: Generate a Self-Signed Certificate for SSL

To enable HTTPS and use a local domain (e.g., `cas.local`), generate a self-signed certificate:

1. **Create a directory for certificates**:
   ```bash
   mkdir -p ./tests/fixtures/certs
   ```
2. **Navigate to the certificates directory**:
   ```bash
   cd ./tests/fixtures/certs
   ```
3. **Generate a private key and certificate**:
   ```bash
   openssl req -x509 -newkey rsa:4096 -keyout cas.key -out cas.crt -days 365 -nodes
   ```
   - When prompted, enter `cas.local` as the **Common Name (CN)**.

4. **Combine the key and certificate into a PKCS12 (PFX) file**:
   ```bash
   openssl pkcs12 -export -in cas.crt -inkey cas.key -out cas.pfx -name "cas" -passout pass:changeit
   ```
5. **Return to the project root directory**:
   ```bash
   cd ../../../
   ```
6. ** Add the self signed certificate to Ubuntu's trusted certificates**:
   ```bash
   sudo cp ./tests/fixtures/certs/cas.crt /usr/local/share/ca-certificates/cas.crt
   sudo update-ca-certificates
   ```
## Step 3: Configure CAS Properties

Ensure `cas.properties` (password and users), `application.yml` (definition of service) and `wildcard.json` files 
are located in the `./tests/fixtures/` directory.

## Step 4: Configure Local Domain (cas.local)

1. To access CAS at `https://cas.local:8443`, add the following line to your `/etc/hosts` file:
   ```plaintext
   127.0.0.1 cas.local
   ```

2. This will map `cas.local` to your localhost, allowing you to use it as a local domain.

## Step 5: Run the CAS Server Container with SSL

Start the CAS server with SSL enabled using the following command:

```bash
docker run --rm -e SERVER_PORT=8443 -p 8443:8443 -v `pwd`/tests/fixtures/services:/etc/cas/services  -v `pwd`/tests/fixtures/certs:/etc/cas/certs -v `pwd`/tests/fixtures/config:/etc/cas/config   --name cas-server apereo/cas:v5.3.10
```

This command will:

- Map port 8443 on your host to port 8443 in the container.
- Mount the certificates directory to `/etc/cas/certs` in the container.
- Mount the `cas.properties` file to `/etc/cas/config/cas.properties` in the container.
- Mount the `application.yml` file to `/etc/cas/config/application.yml` in the container.
- Mount the `wildcard.yml` file to `/etc/cas/services/wildcard.json` in the container.
- Name the container `cas-server`.

## Step 6: Access the CAS Server

After starting the CAS server, access it in your browser at:

```
https://cas.local:8443/cas
```

You should see the CAS login page, indicating the server is running over HTTPS on the local domain.

## Step 7: Configure Moodle for CAS Authentication

1. In Moodle, navigate to **Site administration > Plugins > Authentication > Manage authentication**.
2. Enable **CAS server (SSO)** by clicking the eye icon.
3. Click on **Settings** next to CAS server (SSO).
4. Configure the following settings:
   - **Hostname**: `cas.local`
   - **Base URI**: `/cas`
   - **Port**: `8443`
   - **Version**: `2.0`
5. Save the changes.

## Step 8: Test CAS Authentication

1. Log out of Moodle.
2. Attempt to log in again.
3. You should be redirected to the CAS server login page. Once authenticated, you’ll be redirected back to Moodle as a logged-in user.

## Additional Configurations

For custom configurations, such as specific users or authentication settings, you can extend the image or add a custom `cas.properties` file.

## Cleanup

To stop and remove the CAS server container:

```bash
docker stop cas-server
```

This setup provides a secure CAS environment for testing Moodle's CAS integration on a local domain.

## Important Notes

The CAS itself does not provide information about the users like email and firrname...
so we will need (to save us from using LDAP) to as an admin add the email and first name to the user profile manuall
at first login.


## Running and testing


# Setting Up `dnsmasq` and Android Emulator for Local Development

This guide provides steps to configure `dnsmasq` to handle custom domain names on a local development setup and to make these domains accessible within an Android emulator.

## Prerequisites

- A running Android emulator.
- `dnsmasq` installed on your development machine.
- An Apereo CAS server or other service running locally and accessible via custom domains, e.g., `http://competveteval.local` and `https://cas.local:8443`.

## Steps

### 1. Install `dnsmasq`

#### On macOS:
```bash
brew install dnsmasq
```

#### On Linux (Debian-based):
```bash
sudo apt update
sudo apt install dnsmasq
```

### 2. Configure `dnsmasq` for Custom Domains

1. Open the `dnsmasq` configuration file. (Typically located at `/usr/local/etc/dnsmasq.conf` on macOS and `/etc/dnsmasq.conf` on Linux.)
   ```bash
   sudo nano /etc/dnsmasq.conf
   ```

2. Add entries to map custom domains:
   ```plaintext
   address=/competveteval.local/127.0.0.1 # Or the URL of your Moodle installation
   address=/cas.local/127.0.0.1
   ```

3. Save and close the file.

### 3. Restart `dnsmasq`

#### On macOS:
```bash
sudo brew services restart dnsmasq
```

#### On Linux:
```bash
sudo systemctl restart dnsmasq
```

### 4. Configure `systemd-resolved` to Use `dnsmasq` (Linux only)

If your system is using `systemd-resolved`, configure it to forward specific domain requests to `dnsmasq`.

1. Open `resolved.conf`:
   ```bash
   sudo nano /etc/systemd/resolved.conf
   ```

2. Add or modify the settings:
   ```plaintext
   [Resolve]
   DNS=127.0.0.1
   Domains=~competveteval.local ~cas.local
   ```

3. Restart `systemd-resolved` and `dnsmasq`:
   ```bash
   sudo systemctl restart systemd-resolved
   sudo systemctl restart dnsmasq
   ```

### 5. Update `/etc/resolv.conf` (Linux only)

Link `/etc/resolv.conf` to the resolved configuration if necessary:
```bash
sudo ln -sf /run/systemd/resolve/resolv.conf /etc/resolv.conf
```

### 6. Verify DNS Configuration

Use `nslookup` or `dig` to confirm the domains resolve to `127.0.0.53` or `127.0.0.1` (if no systemd-resolved):
```bash
nslookup competveteval.local
nslookup cas.local
```

Both should return `127.0.0.53`.

### 7. Set Android Emulator DNS to Use `dnsmasq`

When starting the Android emulator, specify `127.0.0.1` as the DNS server:
```bash
emulator -avd YourEmulatorName -dns-server 127.0.0.1
```

Replace `YourEmulatorName` with the actual name of your emulator.

### 8. Test the Setup

1. Start the CAS server or any service mapped to the custom domains (`competveteval.local` and `cas.local`). Ensure they are accessible at `127.0.0.1` on your host.
2. Launch your app in the Android emulator and initiate any flow that relies on `competveteval.local` or `cas.local`.
3. Confirm the Android app resolves the custom domains correctly and completes the flow.

---

### Troubleshooting

- **DNS Issues**: If the custom domains aren’t resolving, double-check `/etc/hosts` and ensure `dnsmasq` is running.
- **Port Forwarding**: If using non-standard ports, consider setting up port forwarding between the emulator and host with `adb reverse`.
- **SSL Certificates**: If using HTTPS with a self-signed certificate, install it on the emulator to avoid SSL errors.
