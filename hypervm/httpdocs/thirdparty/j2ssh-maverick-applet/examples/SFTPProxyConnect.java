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
import java.io.InputStreamReader;

import com.sshtools.net.SocketTransport;
import com.sshtools.sftp.SftpClient;
import com.sshtools.sftp.SftpFile;
import com.sshtools.ssh.HostKeyVerification;
import com.sshtools.ssh.PasswordAuthentication;
import com.sshtools.ssh.SshAuthentication;
import com.sshtools.ssh.SshClient;
import com.sshtools.ssh.SshConnector;
import com.sshtools.ssh.SshException;
import com.sshtools.ssh.SshTunnel;
import com.sshtools.ssh.components.SshPublicKey;

/**
 * This example demonstrates how to proxy an SFTP session over a port forwarding
 * tunnel; this is useful for accessing servers behind a firewall.
 * 
 * @author Lee David Painter
 */
public class SFTPProxyConnect {

	public static void main(String[] args) {

		final BufferedReader reader = new BufferedReader(new InputStreamReader(
				System.in));

		try {

			String proxyServer = "proxy.foo.com";
			String targetServer = "target.foo.com";
			String username = "lee";

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

			/**
			 * Connect to the host
			 */
			final SshClient ssh = con.connect(new SocketTransport(proxyServer,
					22), username);

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
			 * Start a tunnel and proxy another connection over it
			 */
			if (ssh.isAuthenticated()) {

				SshTunnel tunnel = ssh.openForwardingChannel(targetServer, 22,
						"127.0.0.1", 22, "127.0.0.1", 22, null, null);

				SshClient forwardedConnection = con.connect(tunnel, username);

				forwardedConnection.authenticate(pwd);

				SftpClient sftp = new SftpClient(forwardedConnection);

				SftpFile[] children = sftp.ls();

				for (int i = 0; i < children.length; i++)
					System.out.println(SftpClient.formatLongname(children[i]));

				sftp.quit();

				forwardedConnection.disconnect();

			}

			ssh.disconnect();

		} catch (Throwable th) {
			th.printStackTrace();
		}
	}

}
