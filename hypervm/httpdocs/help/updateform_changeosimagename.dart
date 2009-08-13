 Be careful when you set the ostemplate name. The os template name is used to determine the network configuration that's done by hyperVM, and if you set the wrong distro, the networking will stop working. The ostemplate name is of the form [b] distro-version-arch-extra [/b], for instance centos-4-i386-kloxo or fedora-6-i386-cpaneltest.The separator should be hyphen and nothing else, and [b] extra [/b] can be anything, and you can assign to distinguish between the ostemplates.


