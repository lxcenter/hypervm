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
import com.sshtools.publickey.ConsoleKnownHostsKeyVerification;
import com.sshtools.scp.ScpClient;
import com.sshtools.ssh.PasswordAuthentication;
import com.sshtools.ssh.SshAuthentication;
import com.sshtools.ssh.SshClient;
import com.sshtools.ssh.SshConnector;
import com.sshtools.ssh2.Ssh2Client;
import com.sshtools.ssh2.Ssh2Context;

/**
 * This example demonstrates the connection process connecting to an SSH2 server
 * and usage of the SCP client.
 * 
 */
public class ScpConnect {

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

			con.getContext().setHostKeyVerification(
					new ConsoleKnownHostsKeyVerification());
			con.getContext().setPreferredPublicKey(
					Ssh2Context.PUBLIC_KEY_SSHDSS);

			/**
			 * Connect to the host
			 */
			SocketTransport t = new SocketTransport(hostname, port);
			t.setTcpNoDelay(true);

			SshClient ssh = con.connect(t, username);

			Ssh2Client ssh2 = (Ssh2Client) ssh;
			/**
			 * Authenticate the user using password authentication
			 */
			PasswordAuthentication pwd = new PasswordAuthentication();

			do {
				System.out.print("Password: ");
				pwd.setPassword(reader.readLine());
			} while (ssh2.authenticate(pwd) != SshAuthentication.COMPLETE
					&& ssh.isConnected());

			/**
			 * Start a session and do basic IO
			 */
			if (ssh.isAuthenticated()) {

				ScpClient scp = new ScpClient(ssh2);

				/**
				 * create a test file 1
				 */
				File textFile = new File(System.getProperty("user.home"),
						"shining.txt");
				FileOutputStream tout = new FileOutputStream(textFile);

				// Create a file with \r\n as EOL
				for (int i = 0; i < 100; i++) {
					tout.write("All work and no play makes Jack a dull boy\r\n"
							.getBytes());
				}
				tout.close();

				/**
				 * create a test file 2
				 */
				textFile = new File(System.getProperty("user.home"),
						"shining1.txt");
				tout = new FileOutputStream(textFile);

				// Create a file with \r\n as EOL
				for (int i = 0; i < 100; i++) {
					tout.write("All work and no play makes Jack a dull boy\r\n"
							.getBytes());
				}
				tout.close();

				/**
				 * create a test file 3
				 */
				textFile = new File(System.getProperty("user.home"),
						"shining2.txt");
				tout = new FileOutputStream(textFile);

				// Create a file with \r\n as EOL
				for (int i = 0; i < 100; i++) {
					tout.write("All work and no play makes Jack a dull boy\r\n"
							.getBytes());
				}
				tout.close();

				// put file 1
				scp.put("shining.txt", "theshining.txt", false);

				// put files 2 and 3
				String[] testfiles = { "shining1.txt", "shining2.txt" };
				scp.put(testfiles, "", false);

				/**
				 * put all files in the remote directory using *
				 */
				scp.put("*ini*", "", false);
				System.out.println("\nput *ini*\n");

				System.out
						.println("Check that copied all local files to remote, press enter.");
				reader.readLine();
				/**
				 * put all files in the remote directory using *.*
				 */
				scp.put("*.txt*", "", false);
				System.out.println("\nPut *.txt*\n");

			}

		} catch (Throwable th) {
			th.printStackTrace();
		}
	}

}
