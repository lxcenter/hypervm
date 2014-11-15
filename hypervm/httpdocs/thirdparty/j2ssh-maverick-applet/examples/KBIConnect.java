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
import java.io.IOException;
import java.io.InputStream;
import java.io.InputStreamReader;

import com.sshtools.net.SocketTransport;
import com.sshtools.ssh.HostKeyVerification;
import com.sshtools.ssh.SshAuthentication;
import com.sshtools.ssh.SshConnector;
import com.sshtools.ssh.SshException;
import com.sshtools.ssh.SshSession;
import com.sshtools.ssh.components.SshPublicKey;
import com.sshtools.ssh2.KBIAuthentication;
import com.sshtools.ssh2.KBIPrompt;
import com.sshtools.ssh2.KBIRequestHandler;
import com.sshtools.ssh2.Ssh2Client;
import com.sshtools.ssh2.Ssh2Context;

/**
 * This example demonstrates how to use keyboard-interactive authentication
 * 
 * @author Lee David Painter
 */
public class KBIConnect {

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

			// Lets do some host key verification
			HostKeyVerification hkv = new HostKeyVerification() {
				public boolean verifyHost(String hostname, SshPublicKey key) {
					try {
						System.out.println("The connected host's key ("
								+ key.getAlgorithm() + ") is");
						System.out.println(key.getFingerprint());
					} catch (SshException e) {
					}
					return true;
				}
			};

			/**
			 * Set our preferred public key type
			 */
			con.getContext().setHostKeyVerification(hkv);
			con.getContext().setPreferredPublicKey(
					Ssh2Context.PUBLIC_KEY_SSHDSS);

			/**
			 * Connect to the host
			 */
			Ssh2Client ssh = con.connect(new SocketTransport(hostname, port),
					username);

			/**
			 * Display the available authentication methods
			 */
			String[] methods = ssh.getAuthenticationMethods(username);
			for (int i = 0; i < methods.length; i++)
				System.out.println(methods[i]);

			/**
			 * Authenticate the user using password authentication
			 */
			KBIAuthentication kbi = new KBIAuthentication();

			kbi.setKBIRequestHandler(new KBIRequestHandler() {
				public boolean showPrompts(String name, String instruction,
						KBIPrompt[] prompts) {
					try {
						System.out.println(name);
						System.out.println(instruction);
						for (int i = 0; i < prompts.length; i++) {
							System.out.print(prompts[i].getPrompt());
							prompts[i].setResponse(reader.readLine());
						}
						return true;
					} catch (IOException e) {
						e.printStackTrace();
						return false;
					}
				}
			});

			if (ssh.authenticate(kbi) != SshAuthentication.COMPLETE) {
				System.out.println("Authentication failed!");
				System.exit(0);
			}

			/**
			 * Start a session and do basic IO
			 */
			if (ssh.isAuthenticated()) {
				SshSession session = ssh.openSessionChannel();
				session.requestPseudoTerminal("vt100", 80, 24, 0, 0);
				session.startShell();

				final InputStream in = session.getInputStream();
				Thread t = new Thread(new Runnable() {
					public void run() {
						try {
							int read;
							while ((read = in.read()) > -1) {
								System.out.print((char) read);
							}
						} catch (Throwable t1) {
						} finally {
							System.exit(0);
						}
					}

				});
				t.start();
				int read;
				while ((read = System.in.read()) > -1) {
					session.getOutputStream().write(read);
				}

				System.exit(0);
			}

		} catch (Throwable th) {
			th.printStackTrace();
		}
	}

}
