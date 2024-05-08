# CALL Learning - Simple Vagrant Setup for CompetVET App testing VM

# Creating the box

To create the box, you need to have Vagrant and VirtualBox installed.

1. Clone the repository
2. Run `vagrant up` in the root of the repository
3. Wait for the box to be created

# Package the box

To package the box, you need to have Vagrant installed.

1. Run `vagrant package --output competvet.box` in the root of the repository
2. Wait for the box to be packaged
3. The box will be saved as `competvet.box` in the root of the repository
4. You can now distribute the box
5. To add the box to your Vagrant installation, run `vagrant box add competvet competvet.box`


## Testing the front-end

The front-end is only accessible through moodlecompetvettest

To access the server:

``
vargant ssh moodlecompetvet
``
