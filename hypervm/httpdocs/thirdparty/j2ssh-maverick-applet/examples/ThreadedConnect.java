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
import java.io.ByteArrayOutputStream;
import java.io.InputStream;
import java.io.InputStreamReader;

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
 * This example demonstrates using multiple threads to access the API.
 * 
 * @author Lee David Painter
 */
public class ThreadedConnect {

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
			 */
			final SshClient ssh = con.connect(new SocketTransport(hostname,
					port), username);

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

				// Some old SSH2 servers (Solaris) kill the connection after the
				// first
				// session has closed and there are no other sessions started;
				// so to avoid this we create the first session and dont ever
				// use it
				SshSession s = ssh.openSessionChannel();
				s.startShell();

				ThreadPool pool = new ThreadPool();

				for (int i = 0; i < 100; i++) {

					if (!ssh.isConnected())
						break;

					final int num = i;

					pool.addOperation(new Runnable() {

						public void run() {

							System.out.println("Executing session " + num);
							System.out.println(ssh.getChannelCount()
									+ " channels currently open");
							SshSession session = null;
							try {

								// We use a buffered session so we can spawn
								// more threads
								// to test multiple threads access to a single
								// channel
								session = ssh.openSessionChannel();

								if (session.requestPseudoTerminal("vt100", 80,
										24, 0, 0)) {

									session.executeCommand("set");
									InputStream in = session.getInputStream();

									ByteArrayOutputStream out = new ByteArrayOutputStream();
									int read;
									while ((read = in.read()) > -1) {
										if (read > 0)
											out.write(read);
									}

									synchronized (System.out) {
										System.out.write(out.toByteArray());
									}
								} else
									System.out
											.println("Failed to allocate pseudo terminal");
							} catch (Throwable t1) {
								t1.printStackTrace();
							} finally {
								if (session != null)
									session.close();
								System.out.println("Completed session " + num);
							}
						}

					});

				}

			}

		} catch (Throwable th) {
			th.printStackTrace();
		}
	}

}

class ThreadPool {

	Thread t[] = new Thread[5];

	public synchronized void addOperation(Runnable r) {

		int nextThread;
		System.out.println("Adding new operation");
		while ((nextThread = getNextThread()) == -1) {
			try {
				wait();
			} catch (InterruptedException ex) {
			}
		}

		start(r, nextThread);

	}

	public int getNextThread() {
		synchronized (t) {
			for (int i = 0; i < t.length; i++) {
				if (t[i] == null) {
					return i;
				}
			}
		}
		return -1;

	}

	public synchronized void release() {
		notify();
	}

	public synchronized void start(final Runnable r, final int i) {
		t[i] = new Thread() {
			public void run() {

				try {
					r.run();
				} catch (Exception ex) {
				}

				synchronized (t) {
					t[i] = null;
				}

				release();
			}

		};

		t[i].start();
	}
}
