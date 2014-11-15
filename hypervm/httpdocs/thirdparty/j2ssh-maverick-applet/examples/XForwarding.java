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
import java.io.InputStream;
import java.io.InputStreamReader;

import com.sshtools.net.ForwardingClient;
import com.sshtools.net.SocketTransport;
import com.sshtools.ssh.PasswordAuthentication;
import com.sshtools.ssh.SshAuthentication;
import com.sshtools.ssh.SshClient;
import com.sshtools.ssh.SshConnector;
import com.sshtools.ssh.SshSession;

/**
 * This example demonstrates how to perform X forwarding.
 * 
 * @author Lee David Painter
 */
public class XForwarding {

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

			/**
			 * Connect to the host
			 */
			SshClient ssh = con.connect(new SocketTransport(hostname, port),
					username);

			/**
			 * Authenticate the user using password authentication
			 */
			PasswordAuthentication pwd = new PasswordAuthentication();

			do {
				System.out.print("Password: ");
				pwd.setPassword(reader.readLine());
			} while (ssh.authenticate(pwd) != SshAuthentication.COMPLETE
					&& ssh.isConnected());

			// Configure X Forwarding - this must be done before any sessions
			// are opened
			ForwardingClient fwd = new ForwardingClient(ssh);
			fwd.allowX11Forwarding("localhost:0");

			/**
			 * Start a session and do basic IO
			 */
			if (ssh.isAuthenticated()) {

				final SshSession session = ssh.openSessionChannel();
				session.requestPseudoTerminal("vt100", 80, 24, 0, 0);
				session.startShell();

				final InputStream in = session.getInputStream();
				Thread t = new Thread(new Runnable() {
					public void run() {
						try {
							int read;
							while ((read = in.read()) > -1) {
								if (read > 0)
									System.out.print((char) read);
							}
						} catch (Throwable t1) {
							t1.printStackTrace();
						} finally {
							// System.exit(0);
							session.close();
						}
					}

				});
				t.start();
				int read;
				while ((read = System.in.read()) > -1) {
					session.getOutputStream().write(read);
				}

				session.close();
				// System.exit(0);
			}

		} catch (Throwable th) {
			th.printStackTrace();
		}
	}

}
