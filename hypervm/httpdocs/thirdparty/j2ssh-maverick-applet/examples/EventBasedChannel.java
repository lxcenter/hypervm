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
import java.io.InputStreamReader;

import com.sshtools.net.SocketTransport;
import com.sshtools.ssh.ChannelAdapter;
import com.sshtools.ssh.HostKeyVerification;
import com.sshtools.ssh.PasswordAuthentication;
import com.sshtools.ssh.PseudoTerminalModes;
import com.sshtools.ssh.SshAuthentication;
import com.sshtools.ssh.SshChannel;
import com.sshtools.ssh.SshClient;
import com.sshtools.ssh.SshConnector;
import com.sshtools.ssh.SshException;
import com.sshtools.ssh.SshSession;
import com.sshtools.ssh.components.SshPublicKey;
import com.sshtools.ssh2.Ssh2Context;

/**
 * This example demonstrates how to use a channel listener to wait for events to
 * happen on a channel instead of having a thread reading from the client at all
 * times.
 * 
 * @author Lee David Painter
 */
public class EventBasedChannel {

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
						e.printStackTrace();
					}
					return true;
				}
			};

			con.getContext().setHostKeyVerification(hkv);
			con.getContext().setPreferredPublicKey(
					Ssh2Context.PUBLIC_KEY_SSHDSS);

			/**
			 * Connect to the host
			 * 
			 * IMPORTANT: We must use buffered mode so that we have a background
			 * thread to fire data events back to us.
			 */
			final SshClient ssh = con.connect(new SocketTransport(hostname,
					port), username, true);
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

				/**
				 * The following session demonstrates listenening for data
				 * events on a channel
				 */
				ChannelAdapter eventListener = new ChannelAdapter() {

					public void dataReceived(SshChannel channel, byte[] buf,
							int offset, int len) {
						System.out.write(buf, offset, len);
					}

					public synchronized void channelClosed(SshChannel channel) {
						notifyAll();
					}

				};

				final SshSession session = ssh.openSessionChannel();

				session.addChannelEventListener(eventListener);
				// Use the newly added PseudoTerminalModes class to
				// turn off echo on the remote shell
				PseudoTerminalModes pty = new PseudoTerminalModes(ssh);
				pty.setTerminalMode(PseudoTerminalModes.ECHO, false);

				session.requestPseudoTerminal("vt100", 80, 24, 0, 0, pty);

				// Because were using an event based model we dont
				// want the InputStream filling up and deadlocking
				// the session
				session.setAutoConsumeInput(true);

				session.executeCommand("echo Hello World");

				// Now wait for it to complete
				synchronized (eventListener) {
					eventListener.wait();
				}

				/**
				 * The following session demonstrates using the InputStreams
				 * available method to tell when data is ready on a channel
				 */
				final SshSession session2 = ssh
						.openSessionChannel(eventListener);

				// We can reuse the old pty but we can't change it
				session2.requestPseudoTerminal("vt100", 80, 24, 0, 0, pty);
				session2.setAutoConsumeInput(true);
				session2.startShell();

				Thread t = new Thread() {
					public void run() {

						try {
							int read;
							while ((read = System.in.read()) > -1
									&& !session2.isClosed()) {
								session2.getOutputStream().write(read);
							}
						} catch (IOException ex) {
						}
					}
				};

				t.start();

				synchronized (eventListener) {
					eventListener.wait();
				}

				// Force our System.in reader thread to exit when the
				// user presses a key
				System.out.println("Press any key to exit.");
			}

			ssh.disconnect();
		} catch (Throwable t) {
			t.printStackTrace();
		}
	}

}
