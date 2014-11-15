/**
 * Copyright 2003-2014 SSHTOOLS Limited. All Rights Reserved.
 *
 * For product documentation visit https://www.sshtools.com/
 *
 * This file is part of J2SSH Maverick.
 *
 * J2SSH Maverick is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * J2SSH Maverick is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with J2SSH Maverick.  If not, see <http://www.gnu.org/licenses/>.
 */

import java.io.BufferedReader;
import java.io.FileInputStream;
import java.io.IOException;
import java.io.InputStreamReader;
import java.security.KeyStore;
import java.security.cert.X509Certificate;
import java.security.interfaces.RSAPrivateKey;

import com.sshtools.net.SocketTransport;
import com.sshtools.publickey.ConsoleKnownHostsKeyVerification;
import com.sshtools.ssh.PseudoTerminalModes;
import com.sshtools.ssh.PublicKeyAuthentication;
import com.sshtools.ssh.SshAuthentication;
import com.sshtools.ssh.SshClient;
import com.sshtools.ssh.SshConnector;
import com.sshtools.ssh.SshSession;
import com.sshtools.ssh.components.jce.Ssh2RsaPrivateKey;
import com.sshtools.ssh.components.jce.SshX509RsaSha1PublicKey;
import com.sshtools.ssh2.Ssh2Client;

/**
 * This example demonstrates authenticating with x509 certificates.
 * 
 * @author Lee David Painter
 */
public class X509Connect {

	public static void main(String[] args) {

		final BufferedReader reader = new BufferedReader(new InputStreamReader(
				System.in));

		try {

			System.out.print("Hostname: ");
			String hostname = reader.readLine();

			int idx = hostname.indexOf(':');
			int port = 22;
			if (idx > -1) {
				port = Integer.parseInt(hostname.substring(idx + 1));
				hostname = hostname.substring(0, idx);

			}

			System.out.print("Username [Enter for "
					+ System.getProperty("user.name") + "]: ");
			String username = reader.readLine();

			if (username == null || username.trim().equals(""))
				username = System.getProperty("user.name");

			System.out.println("Connecting to " + hostname);

			/**
			 * Create an SshConnector instance
			 */
			SshConnector con = SshConnector.createInstance();

			// Verify server host keys using the users known_hosts file
			con.getContext().setHostKeyVerification(
					new ConsoleKnownHostsKeyVerification());

			System.out.print("Identity: ");
			String ident = reader.readLine();

			System.out.print("Password: ");
			String passphrase = reader.readLine();

			System.out.println("Alias: ");
			String alias = reader.readLine();

			KeyStore keystore = KeyStore.getInstance("PKCS12");

			keystore.load(new FileInputStream(ident), passphrase.toCharArray());

			RSAPrivateKey prv = (RSAPrivateKey) keystore.getKey(alias,
					passphrase.toCharArray());
			X509Certificate x509 = (X509Certificate) keystore
					.getCertificate(alias);

			/**
			 * Connect to the host
			 */
			final SshClient ssh = con.connect(new SocketTransport(hostname,
					port), username);
			System.out.println(ssh.toString());

			String[] methods = ((Ssh2Client) ssh)
					.getAuthenticationMethods(username);

			for (int i = 0; i < methods.length; i++) {
				System.out.println(methods[i]);
			}

			/**
			 * Authenticate the user using password authentication
			 */
			PublicKeyAuthentication pk = new PublicKeyAuthentication();

			pk.setPublicKey(new SshX509RsaSha1PublicKey(x509));
			pk.setPrivateKey(new Ssh2RsaPrivateKey(prv));

			if (ssh.authenticate(pk) != SshAuthentication.COMPLETE) {
				throw new Exception("X509 authentication failed");
			}

			/**
			 * Start a session and do basic IO
			 */
			if (ssh.isAuthenticated()) {

				// Some old SSH2 servers kill the connection after the first
				// session has closed and there are no other sessions started;
				// so to avoid this we create the first session and dont ever
				// use it
				final SshSession session = ssh.openSessionChannel();

				// Use the newly added PseudoTerminalModes class to
				// turn off echo on the remote shell
				PseudoTerminalModes pty = new PseudoTerminalModes(ssh);
				pty.setTerminalMode(PseudoTerminalModes.ECHO, false);

				session.requestPseudoTerminal("vt100", 80, 24, 0, 0, pty);

				session.startShell();

				Thread t = new Thread() {
					public void run() {
						try {
							int read;
							while ((read = session.getInputStream().read()) > -1) {
								System.out.write(read);
								System.out.flush();
							}
						} catch (IOException ex) {
							ex.printStackTrace();
						}
					}
				};

				t.start();
				int read;
				byte[] buf = new byte[4096];
				while ((read = System.in.read(buf)) > -1) {
					session.getOutputStream().write(buf, 0, read);

				}

				session.close();
			}

			ssh.disconnect();
		} catch (Throwable t) {
			t.printStackTrace();
		}
	}

}
