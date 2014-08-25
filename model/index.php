<?php $wordpress_directory = '';
/* Enter the name of the directory where you installed WordPress between the 2 single quotes above. Enter nothing if you installed WordPress in the root of your website.
Entrez le nom du répertoire dans lequel vous avez installé WordPress entre les 2 apostrophes ci-dessus. N'entrez rien si vous avez installé WordPress à la racine de votre site. */
$file = $wordpress_directory.'/wp-load.php';
while (strstr($file, '//')) { $file = str_replace('//', '/', $file); }
if (substr($file, 0, 1) == '/') { $file = substr($file, 1); }
$i = 0; while ((!file_exists($file)) && ($i < 8)) { $file = '../'.$file; $i = $i + 1; }
include_once $file;
/* Personalize the code between "<!DOCTYPE html>" and "</html>". Keep the "<?php wp_head(); ?>" and "<?php wp_footer(); ?>" lines.
Personnalisez le code situé entre "<!DOCTYPE html>" et "</html>", tout en conservant les lignes "<?php wp_head(); ?>" et "<?php wp_footer(); ?>". */ ?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<title>Title</title>
<meta charset="<?php bloginfo('charset'); ?>" />
<?php wp_head(); ?>
</head>
<body>
<?php /* Enter the HTML/PHP code of your page's content just before "<?php wp_footer(); ?>".
Entrez le code HTML/PHP du contenu de votre page juste avant "<?php wp_footer(); ?>". */ ?>

<?php wp_footer(); ?>
</body>
</html>