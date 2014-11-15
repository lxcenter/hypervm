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
import com.sshtools.sftp.SftpClient;
import com.sshtools.sftp.SftpFile;
import com.sshtools.sftp.SftpFileAttributes;
import com.sshtools.ssh.PasswordAuthentication;
import com.sshtools.ssh.SshAuthentication;
import com.sshtools.ssh.SshClient;
import com.sshtools.ssh.SshConnector;
import com.sshtools.ssh2.Ssh2Client;
import com.sshtools.ssh2.Ssh2Context;

/**
 * This example demonstrates the connection process connecting to an SSH2 server
 * and usage of the SFTP client.
 * 
 * @author Lee David Painter
 */
public class SftpConnect {

	public static void main(String[] args) {

		final BufferedReader reader = new BufferedReader(new InputStreamReader(
				System.in));

		try {
			System.out.print("Hostname: ");
			String hostname;
			hostname = reader.readLine();

			int idx = hostname.indexOf(':');
			int port = 22;
			if (idx > -1) {
				port = Integer.parseInt(hostname.substring(idx + 1));
				hostname = hostname.substring(0, idx);

			}

			System.out.print("Username [Enter for "
					+ System.getProperty("user.name") + "]: ");

			String username;
			username = reader.readLine();

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

				SftpClient sftp = new SftpClient(ssh2);

				/**
				 * Perform some text mode operations
				 */
				sftp.setTransferMode(SftpClient.MODE_TEXT);

				File textFile = new File(System.getProperty("user.home"),
						"shining.txt");
				FileOutputStream tout = new FileOutputStream(textFile);

				// Create a file with \r\n as EOL
				for (int i = 0; i < 100; i++) {
					tout.write("All work and no play makes Jack a dull boy\r\n"
							.getBytes());
				}

				tout.close();

				// Tell the client which EOL the remote client is using - note
				// that this will be ignored with version 4 of the protocol
				sftp.setRemoteEOL(SftpClient.EOL_LF);

				// Now put the file, the remote file should end up with all \r\n
				// changed to \n
				sftp.put(textFile.getAbsolutePath());

				/**
				 * Now perform some binary operations
				 */
				sftp.setTransferMode(SftpClient.MODE_BINARY);

				/**
				 * List the contents of the directory
				 */
				SftpFile[] ls = sftp.ls();
				for (int i = 0; i < ls.length; i++) {
					ls[i].getParent();
					System.out.println(SftpClient.formatLongname(ls[i]));
				}
				/**
				 * Generate a temporary file for uploading/downloading
				 */
				File f = new File(System.getProperty("user.home"), "sftp-file");
				java.util.Random rnd = new java.util.Random();

				FileOutputStream out = new FileOutputStream(f);
				byte[] buf = new byte[4096];
				for (int i = 0; i < 5000; i++) {
					rnd.nextBytes(buf);
					out.write(buf);
				}
				out.close();
				/**
				 * Create a directory
				 */
				sftp.mkdirs("sftp/test-files");

				/**
				 * Change directory
				 */
				sftp.cd("sftp/test-files");

				/**
				 * Put a file into our new directory
				 */
				long length = f.length();
				System.out.println("Putting file");
				long t1 = System.currentTimeMillis();
				sftp.put(f.getAbsolutePath());
				long t2 = System.currentTimeMillis();
				System.out.println("Completed.");
				long e = t2 - t1;
				System.out.println("Took " + String.valueOf(e)
						+ " milliseconds");
				float kbs;
				if (e >= 1000) {
					kbs = ((float) length / 1024) / ((float) e / 1000);
					System.out.println("Upload Transfered at "
							+ String.valueOf(kbs) + " kbs");
				}
				/**
				 * Get the attributes of the uploaded file
				 */
				System.out.println("Getting attributes of the remote file");
				SftpFileAttributes attrs = sftp.stat(f.getName());
				System.out
						.println(SftpClient.formatLongname(attrs, f.getName()));

				/**
				 * Download the file inot a new location
				 */
				File f2 = new File(System.getProperty("user.home"),
						"downloaded");
				f2.mkdir();

				sftp.lcd(f2.getAbsolutePath());

				System.out.println("Getting file");
				t1 = System.currentTimeMillis();
				sftp.get(f.getName());
				t2 = System.currentTimeMillis();
				System.out.println("Completed.");
				e = t2 - t1;
				System.out.println("Took " + String.valueOf(e)
						+ " milliseconds");
				if (e >= 1000) {
					kbs = ((float) length / 1024) / ((float) e / 1000);
					System.out.println("Download Transfered at "
							+ String.valueOf(kbs) + " kbs");
				}

				/**
				 * Set the permissions on the file and check they were changed
				 * they should be -rw-r--r--
				 */
				sftp.chmod(0644, f.getName());
				attrs = sftp.stat(f.getName());
				System.out
						.println(SftpClient.formatLongname(attrs, f.getName()));

				sftp.lcd(System.getProperty("user.home"));
				System.out.println(sftp.lpwd());
				File f3 = new File(System.getProperty("user.home"), "testfiles");
				f3.mkdir();
				sftp.lcd("testfiles");
				sftp.cd("");
				/**
				 * get a file using getFiles with default no reg exp matching
				 */
				SftpFile[] remotefiles = sftp.ls();
				if (remotefiles.length > 2) {
					int i = 0;
					while ((remotefiles[i].getFilename().equals(".") | remotefiles[i]
							.getFilename().equals(".."))
							& (i < remotefiles.length)) {
						i++;
					}
					System.out.println("\n first remote filename"
							+ remotefiles[i].getFilename());
					sftp.getFiles(remotefiles[i].getFilename());
					System.out.println("\nGot " + remotefiles[i].getFilename()
							+ "\n");
				}

				// change reg exp syntax from default SftpClient.NoSyntax (no
				// reg exp matching) to SftpClient.GlobSyntax
				sftp.setRegularExpressionSyntax(SftpClient.GlobSyntax);

				/**
				 * get all files in the remote directory using *.*
				 */
				sftp.getFiles("*.txt");
				System.out.println("\nGot *.txt\n");

				System.out
						.println("Check that copied all remote txt files to local, press enter.");
				reader.readLine();

				/**
				 * get all files in the remote directory using *
				 */
				sftp.getFiles("*");
				System.out.println("\nGot *\n");

				System.out
						.println("Check that copied all remote files to local, press enter.");
				reader.readLine();

				/**
				 * put all txt files in the local directory into the remote
				 * directory using *.txt
				 */
				sftp.putFiles("*.txt");
				System.out.println("\nPut *.txt\n");

			}
		} catch (Throwable th) {
			th.printStackTrace();
		}
	}
}
