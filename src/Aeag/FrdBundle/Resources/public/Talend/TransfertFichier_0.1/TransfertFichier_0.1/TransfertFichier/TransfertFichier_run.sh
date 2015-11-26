cd `dirname $0`
 ROOT_PATH=`pwd`
java -Xms256M -Xmx1024M -cp classpath.jar: ext_gac_import.transfertfichier_0_1.TransfertFichier --context=Default $* 