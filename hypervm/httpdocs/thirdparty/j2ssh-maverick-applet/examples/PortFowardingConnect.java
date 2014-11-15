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
import com.sshtools.ssh.HostKeyVerification;
import com.sshtools.ssh.PasswordAuthentication;
import com.sshtools.ssh.SshAuthentication;
import com.sshtools.ssh.SshClient;
import com.sshtools.ssh.SshConnector;
import com.sshtools.ssh.SshException;
import com.sshtools.ssh.SshSession;
import com.sshtools.ssh.components.SshPublicKey;
import com.sshtools.ssh2.Ssh2Context;

/**
 * This example demonstrates how to perform port forwarding.
 * 
 * @author Lee David Painter
 */
public class PortFowardingConnect {

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

			con.getContext().setHostKeyVerification(hkv);

			con.getContext().setPreferredPublicKey(
					Ssh2Context.PUBLIC_KEY_SSHDSS);

			/**
			 * Connect to the host
			 */
			SshClient ssh = con.connect(new SocketTransport(hostname, port),
					username, true);

			/**
			 * Authenticate the user using password authentication
			 */
			PasswordAuthentication pwd = new PasswordAuthentication();

			do {
				System.out.print("Password: ");
				pwd.setPassword(reader.readLine());
			} while (ssh.authenticate(pwd) != SshAuthentication.COMPLETE
					&& ssh.isConnected());

			/**
			 * Start a session and do basic IO
			 */
			if (ssh.isAuthenticated()) {

				final ForwardingClient fwd = new ForwardingClient(ssh);

				if (!fwd.requestRemoteForwarding("127.0.0.1", 8080,
						"127.0.0.1", 80)) {
					System.out.println("Forwarding request failed!");
				}

				/**
				 * Start the users session. It also acts as a thread to service
				 * incoming channel requests for the port forwarding for both
				 * versions. Since we have a single threaded API we have to do
				 * this to send a timely response
				 */
				final SshSession session = ssh.openSessionChannel();
				session.requestPseudoTerminal("vt100", 80, 24, 0, 0);
				session.startShell();

				/**
				 * Start local forwardings after starting the users session.
				 */
				fwd.startLocalForwarding("127.0.0.1", 8080, "cnn.com", 80);

				final InputStream in = session.getInputStream();
				Thread t = new Thread() {
					public void run() {
						try {
							int read;
							while ((read = in.read()) > -1) {
								if (read > 0)
									System.out.print((char) read);
							}
						} catch (Throwable t4) {
							t4.printStackTrace();
						} finally {
							System.exit(0);
						}
					}

				};
				t.start();

				/**
				 * Were also going to have a monitor thread that displays the
				 * active forwardings and tunnels every 10 seconds
				 */
				Thread m = new Thread() {
					public void run() {
						while (!session.isClosed()) {
							try {
								Thread.sleep(10000);
							} catch (Throwable t3) {
							}

							try {
								String[] listeners = fwd.getLocalForwardings();
								for (int i = 0; i < listeners.length; i++) {
									System.out.print("Local forwarding "
											+ listeners[i]);
									try {
										ForwardingClient.ActiveTunnel[] tun1 = fwd
												.getLocalForwardingTunnels(listeners[i]);
										System.out.println(" has "
												+ String.valueOf(tun1.length)
												+ " active tunnels");
									} catch (Throwable t2) {
										t2.printStackTrace();
									}
								}

								listeners = fwd.getRemoteForwardings();
								for (int i = 0; i < listeners.length; i++) {
									System.out.print("Remote forwarding "
											+ listeners[i]);
									try {
										ForwardingClient.ActiveTunnel[] tun2 = fwd
												.getRemoteForwardingTunnels(listeners[i]);
										System.out.println(" has "
												+ String.valueOf(tun2.length)
												+ " active tunnels");
									} catch (Throwable t5) {
										t5.printStackTrace();
									}
								}

							} catch (Throwable t1) {
								t1.printStackTrace();
							}

						}
					}
				};
				m.start();
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
