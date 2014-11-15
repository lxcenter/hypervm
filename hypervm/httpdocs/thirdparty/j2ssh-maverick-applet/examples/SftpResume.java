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
import java.io.File;
import java.io.FileOutputStream;
import java.io.InputStreamReader;

import com.sshtools.net.SocketTransport;
import com.sshtools.sftp.SftpClient;
import com.sshtools.ssh.PasswordAuthentication;
import com.sshtools.ssh.SshAuthentication;
import com.sshtools.ssh.SshClient;
import com.sshtools.ssh.SshConnector;

/**
 * This example demonstrates the connection process connecting to an SSH2 server
 * and usage of the SFTP client.
 * 
 * @author Lee David Painter
 */
public class SftpResume {

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

			/**
			 * Start a session and do basic IO
			 */
			if (ssh.isAuthenticated()) {

				/**
				 * IMPORTANT: for this demonstration the file must be of
				 * sufficent size to still be in progress after 3 seconds.
				 * 
				 * If the file is not big enough increase the size by amending
				 * the loop that creates the file
				 */
				final File f = new File(System.getProperty("user.home"),
						"sftp-resume");
				java.util.Random rnd = new java.util.Random();

				FileOutputStream out = new FileOutputStream(f);
				byte[] buf = new byte[4096];
				for (int i = 0; i < 5000; i++) {
					rnd.nextBytes(buf);
					out.write(buf);
				}
				out.close();

				final SftpClient sftp = new SftpClient(ssh);

				Thread t = new Thread() {
					public void run() {
						try {
							sftp.put(f.getAbsolutePath());
						} catch (Throwable ex) {
							System.out
									.println("The upload has been interrupted");
						}
					}
				};

				// Start the upload thread, wait and then interrupt
				t.start();
				Thread.sleep(3000);

				// Force the SFTP client to quit leaving a file
				// that is not fully uploaded
				sftp.quit();

				// Open up an SFTP client again
				final SftpClient sftp2 = new SftpClient(ssh);

				// Put the file again instructing the client to resume
				sftp2.put(f.getAbsolutePath(), true);

				System.out.println("The upload has been completed");

				// Now start a download
				Thread t2 = new Thread() {
					public void run() {
						try {
							sftp2.get(f.getName(),
									System.getProperty("user.home")
											+ "/sftp-resume-downloaded");
						} catch (Throwable ex) {
							System.out
									.println("The download has been interrupted");
						}
					}
				};

				// Start the upload thread, wait and then interrupt
				t2.start();
				Thread.sleep(3000);

				sftp2.quit();

				SftpClient sftp3 = new SftpClient(ssh);

				sftp3.get(f.getName(), System.getProperty("user.home")
						+ "/sftp-resume-downloaded", true);

				System.out.println("The download has completed");
			}

		} catch (Throwable th) {
			th.printStackTrace();
		}
	}

}
