<?php
/*
Plugin Name: SeadePibGraph
Plugin URI: 
Description: Upload and parse CSV and generate Google Chart
Version: 0.0.1
Author: Alexandre Cabrera
Author Email: ale.116@gmail.com
License:

  Copyright 2014 Alexandre Cabrera <ale.116@gmail.com>

  This program is free software; you can redistribute it and/or modify
  it under the terms of the Creative Commons Attribution-NonCommercial 4.0 
  International (CC BY-NC 4.0)
*/

include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

class SeadePibGraph {

	/*--------------------------------------------*
	 * Constants
	 *--------------------------------------------*/
	const name = 'SeadePibGraph';
	const slug = 'seadepibgraph';
	
	/**
	 * Constructor
	 */
	function __construct() {
		//register an activation hook for the plugin
		register_activation_hook( __FILE__, array( &$this, 'install_seadepibgraph' ) );
                register_activation_hook( __FILE__, array( &$this, 'cria_tabela_csv_arqs' ) );
                register_activation_hook( __FILE__, array( &$this, 'cria_tabela_dados_graph' ) );
                register_activation_hook( __FILE__, array( &$this, 'cria_tabelas_auxiliares' ) );

		//Hook up to the init action
		add_action( 'init', array( &$this, 'init_seadepibgraph' ) );
	}
  
	/**
	 * Runs when the plugin is activated
	 */  
	function install_seadepibgraph() {
		// do not generate any output here
	}
  
	/**
	 * Runs when the plugin is initialized
	 */
	function init_seadepibgraph() {
		// Setup localization
		load_plugin_textdomain( self::slug, false, dirname( plugin_basename( __FILE__ ) ) . '/lang' );
		// Load JavaScript and stylesheets
		$this->register_scripts_and_styles();

		// Register the shortcode [graph]
                add_shortcode( 'graph', array( &$this, 'render_graph' ) );

                // add more buttons to the html editor
                add_action( 'admin_print_footer_scripts', array( &$this, 'seadepibgraph_add_quicktags' ));
                
                // admiin posts
                add_action( 'admin_post_ins_graph_show', array( &$this, 'ins_graph_show' ));
                add_action( 'admin_post_get_lista_graph', array(&$this, 'get_lista_graph') );
                add_action( 'admin_post_get_monta_graph', array(&$this, 'get_monta_graph') );
                add_action( 'admin_post_ins_lista_graph', array(&$this, 'ins_lista_graph') );
                add_action( 'admin_post_preview_graph', array(&$this, 'preview_graph') );
                add_action( 'admin_post_lista_localidades', array(&$this, 'lista_localidades') );

                if ( is_admin() ) {
			add_action( 'admin_menu', array( &$this, 'register_seadepibgraph_menu_page') );
			wp_enqueue_script('jquery'); 
			wp_enqueue_script('jquery-ui-core');
			wp_enqueue_script('jquery-ui-accordion');
		} else {
			//this will run when on the frontend
		}

		/*
		 * TODO: Define custom functionality for your plugin here
		 *
		 * For more information: 
		 * http://codex.wordpress.org/Plugin_API#Hooks.2C_Actions_and_Filters
		 */
		//add_action( 'your_action_here', array( &$this, 'action_callback_method_name' ) );
		//add_filter( 'your_filter_here', array( &$this, 'filter_callback_method_name' ) );    
	}

	/**
		Register the SeadePibGraph admin page.
	*/
	function register_seadepibgraph_menu_page(){
		add_menu_page( 'SeadePibGraph menu', 'Criar Gráficos', 'manage_options', 'seadepibgraph/admin/index.php', '', 'dashicons-chart-area', 81 );
}
	/**
	 * Cria tabela para o upload dos arquivos
	 */
        function cria_tabela_csv_arqs(){
            global $wpdb;

            $table_name = $wpdb->prefix . "csv_arqs";
	
            $charset_collate = $wpdb->get_charset_collate();

            $sql = "CREATE TABLE $table_name (
                    id int UNSIGNED NOT NULL AUTO_INCREMENT,
                    name tinytext NOT NULL,
                    type varchar(50) NOT NULL,
                    size bigint NOT NULL,
                    md5 varchar(32) NOT NULL,
                    slug tinytext NOT NULL,
                    PRIMARY KEY (id)
            ) $charset_collate;";

            require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
            dbDelta( $sql );
        }
  
	/**
	 * Cria tabela para o registro de dados para o gráfico
	 */
        function cria_tabela_dados_graph(){
            global $wpdb;

            $table_name = $wpdb->prefix . "dados_graph";
	
            $charset_collate = $wpdb->get_charset_collate();

            $sql = "CREATE TABLE $table_name (
                    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                    id_csv_arq INT UNSIGNED NOT NULL,
                    id_loc smallint(5) NOT NULL,
                    id_var smallint(5) NOT NULL,
                    ano smallint(4) NOT NULL,
                    valor varchar(50) NULL,
                    PRIMARY KEY (id)
            ) $charset_collate;";

            require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
            dbDelta( $sql );
        }

	/**
	 * Cria tabelas auxiliares para o registro de dados para o gráfico
	 */
        function cria_tabelas_auxiliares(){
            global $wpdb;

            $charset_collate = $wpdb->get_charset_collate();

            $sql = "CREATE TABLE " . $wpdb->prefix . "tb_label (
                    id int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                    nome varchar(255) DEFAULT NULL,
                    PRIMARY KEY (id)
            ) $charset_collate;
                CREATE TABLE " . $wpdb->prefix . "tb_localidade (
                    id smallint(5) unsigned NOT NULL DEFAULT '0',
                    pai smallint(5) unsigned NOT NULL DEFAULT '0',
                    nome varchar(250) NOT NULL DEFAULT '',
                    nivel tinyint(3) unsigned NOT NULL DEFAULT '0',
                    ordem smallint(5) unsigned NOT NULL DEFAULT '0',
                    cod_ibge int(11) NOT NULL DEFAULT '0',
                    PRIMARY KEY (id,pai),
                    KEY pai (pai),
                    KEY nivel (nivel),
                    KEY ordem (ordem),
                    KEY nome (nome)
            ) $charset_collate;
                CREATE TABLE " . $wpdb->prefix . "tb_unidade (
                    id smallint(6) UNSIGNED NOT NULL DEFAULT '0',
                    nome varchar(150) NOT NULL,
                    PRIMARY KEY (id)
            ) $charset_collate;
                CREATE TABLE " . $wpdb->prefix . "tb_variavel (
                    id int(10) unsigned NOT NULL AUTO_INCREMENT,
                    nome varchar(255) NOT NULL,
                    id_unid smallint(6) NOT NULL DEFAULT '0',
                    id_label INT(11) UNSIGNED NOT NULL,
                    PRIMARY KEY (id)
            ) $charset_collate;
                CREATE TABLE " . $wpdb->prefix . "tb_variavel_grafico (
                    id_var int(10) unsigned NOT NULL,
                    id_grafico INT(11) UNSIGNED NOT NULL,
                    ordem int(10) unsigned NOT NULL DEFAULT '0',
                    id_unid smallint(6) NOT NULL DEFAULT '0',
                    id_label INT(11) UNSIGNED NOT NULL,
                    PRIMARY KEY (id_var, id_grafico)
            ) $charset_collate;
                CREATE TABLE " . $wpdb->prefix . "tb_grafico (
                    id int(11) unsigned NOT NULL AUTO_INCREMENT,
                    nome varchar(255) NOT NULL,
                    complemento_nome varchar(50) DEFAULT NULL,
                    tipo char(15) NOT NULL,
                    todos_anos enum('N','S') NOT NULL DEFAULT 'N',
                    id_unid smallint(6) NOT NULL DEFAULT '0',
                    PRIMARY KEY (id)
            ) $charset_collate;
                CREATE TABLE " . $wpdb->prefix . "tb_lista_graph (
                    id int(11) unsigned NOT NULL AUTO_INCREMENT,
                    codigo text NOT NULL,
                    PRIMARY KEY (id)
            ) $charset_collate;";
            

            require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
            dbDelta( $sql );
            $this->insere_dados_iniciais();
        }

        /**
	 * Cria tabelas auxiliares para o registro de dados para o gráfico
	 */
        function insere_dados_iniciais(){
            global $wpdb;

            $charset_collate = $wpdb->get_charset_collate();

            $sql = "
                INSERT INTO " . $wpdb->prefix . "tb_label
                    VALUES (1,'Jan. '),(2,'Fev. '),(3,'Mar. '),(4,'Abr. '),(5,'Maio'),(6,'Jun. '),(7,'Jul. '),(8,'Ago. '),(9,'Set. '),(10,'Out. '),(11,'Nov. '),(12,'Dez. '),(13,'Agropecuária'),(19,'Serviços'),(14,'Indústria'),(23,'VA'),(27,'PIB');
                INSERT INTO " . $wpdb->prefix . "tb_localidade
                    VALUES (1,701,'Adamantina',70,'416',3500105),(10,731,'Altinópolis',70,'489',3501004),(100,705,'Cândido Mota',70,'362',3510005),(1000,0,'Estado de São Paulo',0,'001',1000),(101,704,'Cândido Rodrigues',70,'051',3510104),(102,720,'Capão Bonito',70,'724',3510203),(103,719,'Capela do Alto',70,'710',3510302),(104,728,'Capivari',70,'246',3510401),(105,712,'Caraguatatuba',70,'629',3510500),(106,681,'Carapicuíba',70,'011',3510609),(107,742,'Cardoso',70,'614',3510708),(108,735,'Casa Branca',70,'269',3510807),(109,731,'Cássia dos Coqueiros',70,'493',3510906),(11,703,'Alto Alegre',70,'089',3501103),(110,702,'Castilho',70,'077',3511003),(111,713,'Catanduva',70,'528',3511102),(112,713,'Catiguá',70,'529',3511201),(113,737,'Cedral',70,'583',3511300),(114,706,'Cerqueira César',70,'678',3511409),(115,719,'Cerquilho',70,'711',3511508),(116,719,'Cesário Lange',70,'712',3511607),(117,728,'Charqueada',70,'247',3511706),(118,727,'Chavantes',70,'391',3557204),(119,703,'Clementina',70,'100',3511904),(12,742,'Álvares Florence',70,'612',3501202),(120,707,'Colina',70,'126',3512001),(121,707,'Colômbia',70,'127',3512100),(122,724,'Conchal',70,'237',3512209),(123,709,'Conchas',70,'696',3512308),(124,724,'Cordeirópolis',70,'238',3512407),(125,703,'Coroados',70,'101',3512506),(126,706,'Coronel Macedo',70,'679',3512605),(127,732,'Corumbataí',70,'259',3512704),(128,711,'Cosmópolis',70,'311',3512803),(129,742,'Cosmorama',70,'615',3512902),(13,729,'Álvares Machado',70,'441',3501301),(130,681,'Cotia',70,'012',3513009),(131,731,'Cravinhos',70,'494',3513108),(132,717,'Cristais Paulista',70,'287',3513207),(133,705,'Cruzália',70,'363',3513306),(134,714,'Cruzeiro',70,'637',3513405),(135,733,'Cubatão',70,'517',3513504),(136,718,'Cunha',70,'646',3513603),(137,734,'Descalvado',70,'067',3513702),(138,681,'Diadema',70,'013',3513801),(139,735,'Divinolândia',70,'270',3513900),(14,726,'Álvaro de Carvalho',70,'374',3501400),(140,704,'Dobrada',70,'052',3514007),(141,722,'Dois Córregos',70,'167',3514106),(142,721,'Dolcinópolis',70,'560',3514205),(143,734,'Dourado',70,'068',3514304),(144,715,'Dracena',70,'429',3514403),(145,708,'Duartina',70,'150',3514502),(146,731,'Dumont',70,'495',3514601),(147,726,'Echaporã',70,'376',3514700),(148,730,'Eldorado',70,'476',3514809),(149,728,'Elias Fausto',70,'248',3514908),(15,726,'Alvinlândia',70,'375',3501509),(150,681,'Embu das Artes',70,'014',3515004),(151,681,'Embu-Guaçu',70,'015',3515103),(152,716,'Estrela d*Oeste',70,'544',3515202),(153,729,'Estrela do Norte',70,'446',3515301),(154,706,'Fartura',70,'680',3515400),(155,716,'Fernandópolis',70,'545',3515509),(156,704,'Fernando Prestes',70,'053',3515608),(157,681,'Ferraz de Vasconcelos',70,'016',3515707),(158,701,'Flora Rica',70,'417',3515806),(159,742,'Floreal',70,'616',3515905),(16,711,'Americana',70,'308',3501608),(160,701,'Flórida Paulista',70,'418',3516002),(161,705,'Florínia',70,'364',3516101),(162,717,'Franca',70,'288',3516200),(163,681,'Francisco Morato',70,'017',3516309),(164,681,'Franco da Rocha',70,'018',3516408),(165,703,'Gabriel Monteiro',70,'102',3516507),(166,726,'Gália',70,'378',3516606),(167,726,'Garça',70,'379',3516705),(168,703,'Gastão Vidigal',70,'103',3516804),(169,703,'General Salgado',70,'104',3516903),(17,704,'Américo Brasiliense',70,'047',3501707),(170,725,'Getulina',70,'175',3517000),(171,703,'Glicério',70,'105',3517109),(172,725,'Guaiçara',70,'176',3517208),(173,725,'Guaimbê',70,'177',3517307),(174,707,'Guaíra',70,'129',3517406),(175,737,'Guapiaçu',70,'584',3517505),(176,720,'Guapiara',70,'725',3517604),(177,717,'Guará',70,'289',3517703),(178,702,'Guaraçaí',70,'078',3517802),(179,707,'Guaraci',70,'130',3517901),(18,742,'Américo de Campos',70,'613',3501806),(180,716,'Guarani d*Oeste',70,'546',3518008),(181,725,'Guarantã',70,'178',3518107),(182,703,'Guararapes',70,'106',3518206),(183,681,'Guararema',70,'019',3518305),(184,718,'Guaratinguetá',70,'647',3518404),(185,719,'Guareí',70,'713',3518503),(186,731,'Guariba',70,'496',3518602),(187,733,'Guarujá',70,'518',3518701),(188,681,'Guarulhos',70,'020',3518800),(189,703,'Guzolândia',70,'107',3518909),(19,710,'Amparo',70,'187',3501905),(190,741,'Herculândia',70,'405',3519006),(191,708,'Iacanga',70,'151',3519105),(192,741,'Iacri',70,'406',3519204),(193,734,'Ibaté',70,'069',3519303),(194,737,'Ibirá',70,'585',3519402),(195,705,'Ibirarema',70,'365',3519501),(196,704,'Ibitinga',70,'055',3519600),(197,739,'Ibiúna',70,'742',3519709),(198,737,'Icém',70,'586',3519808),(199,729,'Iepê',70,'448',3519907),(2,737,'Adolfo',70,'580',3500204),(20,732,'Analândia',70,'257',3502002),(200,722,'Igaraçu do Tietê',70,'168',3520004),(201,717,'Igarapava',70,'290',3520103),(202,738,'Igaratá',70,'654',3520202),(203,730,'Iguape',70,'477',3520301),(204,712,'Ilhabela',70,'630',3520400),(205,711,'Indaiatuba',70,'315',3520509),(206,729,'Indiana',70,'449',3520608),(207,716,'Indiaporã',70,'547',3520707),(208,701,'Inúbia Paulista',70,'419',3520806),(209,727,'Ipaussu',70,'393',3520905),(21,702,'Andradina',70,'076',3502101),(210,739,'Iperó',70,'743',3521002),(211,732,'Ipeúna',70,'260',3521101),(212,720,'Iporanga',70,'726',3521200),(213,736,'Ipuã',70,'302',3521309),(214,724,'Iracemápolis',70,'239',3521408),(215,713,'Irapuã',70,'531',3521507),(216,701,'Irapuru',70,'420',3521606),(217,720,'Itaberá',70,'727',3521705),(218,706,'Itaí',70,'682',3521804),(219,713,'Itajobi',70,'532',3521903),(22,719,'Angatuba',70,'707',3502200),(220,722,'Itaju',70,'169',3522000),(221,733,'Itanhaém',70,'519',3522109),(222,681,'Itapecerica da Serra',70,'021',3522208),(223,719,'Itapetininga',70,'714',3522307),(224,720,'Itapeva',70,'729',3522406),(225,681,'Itapevi',70,'022',3522505),(226,711,'Itapira',70,'212',3522604),(227,704,'Itápolis',70,'056',3522703),(228,706,'Itaporanga',70,'683',3522802),(229,722,'Itapuí',70,'170',3522901),(23,709,'Anhembi',70,'692',3502309),(230,702,'Itapura',70,'080',3523008),(231,681,'Itaquaquecetuba',70,'023',3523107),(232,720,'Itararé',70,'731',3523206),(233,730,'Itariri',70,'479',3523305),(234,723,'Itatiba',70,'316',3523404),(235,709,'Itatinga',70,'697',3523503),(236,732,'Itirapina',70,'261',3523602),(237,717,'Itirapuã',70,'291',3523701),(238,735,'Itobi',70,'272',3523800),(239,739,'Itu',70,'744',3523909),(24,729,'Anhumas',70,'442',3502408),(240,723,'Itupeva',70,'229',3524006),(241,717,'Ituverava',70,'292',3524105),(242,707,'Jaborandi',70,'131',3524204),(243,731,'Jaboticabal',70,'498',3524303),(244,738,'Jacareí',70,'655',3524402),(245,737,'Jaci',70,'588',3524501),(246,730,'Jacupiranga',70,'480',3524600),(247,711,'Jaguariúna',70,'317',3524709),(248,721,'Jales',70,'561',3524808),(249,738,'Jambeiro',70,'656',3524907),(25,718,'Aparecida',70,'643',3502507),(250,681,'Jandira',70,'024',3525003),(251,731,'Jardinópolis',70,'499',3525102),(252,723,'Jarinu',70,'230',3525201),(253,722,'Jaú',70,'171',3525300),(254,717,'Jeriquara',70,'293',3525409),(255,710,'Joanópolis',70,'191',3525508),(256,741,'João Ramalho',70,'407',3525607),(257,737,'José Bonifácio',70,'589',3525706),(258,726,'Júlio Mesquita',70,'380',3525805),(259,723,'Jundiaí',70,'231',3525904),(26,721,'Aparecida d*Oeste',70,'557',3502606),(260,715,'Junqueirópolis',70,'430',3526001),(261,730,'Juquiá',70,'481',3526100),(262,681,'Juquitiba',70,'025',3526209),(263,740,'Lagoinha',70,'663',3526308),(264,709,'Laranjal Paulista',70,'698',3526407),(265,702,'Lavínia',70,'081',3526506),(266,714,'Lavrinhas',70,'638',3526605),(267,724,'Leme',70,'240',3526704),(268,708,'Lençóis Paulista',70,'152',3526803),(269,724,'Limeira',70,'241',3526902),(27,720,'Apiaí',70,'720',3502705),(270,710,'Lindóia',70,'192',3527009),(271,725,'Lins',70,'179',3527108),(272,718,'Lorena',70,'648',3527207),(273,723,'Louveira',70,'232',3527306),(274,701,'Lucélia',70,'421',3527405),(275,708,'Lucianópolis',70,'153',3527504),(276,731,'Luís Antônio',70,'500',3527603),(277,703,'Luiziânia',70,'109',3527702),(278,726,'Lupércio',70,'381',3527801),(279,705,'Lutécia',70,'366',3527900),(28,703,'Araçatuba',70,'090',3502804),(280,708,'Macatuba',70,'154',3528007),(281,742,'Macaubal',70,'617',3528106),(282,716,'Macedônia',70,'548',3528205),(283,742,'Magda',70,'618',3528304),(284,739,'Mairinque',70,'746',3528403),(285,681,'Mairiporã',70,'026',3528502),(286,706,'Manduri',70,'684',3528601),(287,729,'Marabá Paulista',70,'450',3528700),(288,705,'Maracaí',70,'367',3528809),(289,701,'Mariápolis',70,'422',3528908),(29,739,'Araçoiaba da Serra',70,'741',3502903),(290,726,'Marília',70,'382',3529005),(291,721,'Marinópolis',70,'562',3529104),(292,729,'Martinópolis',70,'451',3529203),(293,704,'Matão',70,'057',3529302),(294,681,'Mauá',70,'027',3529401),(295,737,'Mendonça',70,'590',3529500),(296,716,'Meridiano',70,'549',3529609),(297,717,'Miguelópolis',70,'294',3529708),(298,722,'Mineiros do Tietê',70,'172',3529807),(299,730,'Miracatu',70,'482',3529906),(3,735,'Aguaí',70,'266',3500303),(30,717,'Aramina',70,'284',3503000),(300,716,'Mira Estrela',70,'550',3530003),(301,702,'Mirandópolis',70,'082',3530102),(302,729,'Mirante do Paranapanema',70,'452',3530201),(303,737,'Mirassol',70,'591',3530300),(304,737,'Mirassolândia',70,'592',3530409),(305,735,'Mococa',70,'273',3530508),(306,681,'Mogi das Cruzes',70,'028',3530607),(307,711,'Mogi Guaçu',70,'214',3530706),(308,711,'Moji Mirim',70,'215',3530805),(309,728,'Mombuca',70,'249',3530904),(31,706,'Arandu',70,'675',3503109),(310,742,'Monções',70,'619',3531001),(311,733,'Mongaguá',70,'520',3531100),(312,710,'Monte Alegre do Sul',70,'193',3531209),(313,731,'Monte Alto',70,'501',3531308),(314,737,'Monte Aprazível',70,'593',3531407),(315,707,'Monte Azul Paulista',70,'132',3531506),(316,715,'Monte Castelo',70,'431',3531605),(317,738,'Monteiro Lobato',70,'657',3531704),(318,711,'Monte Mor',70,'318',3531803),(319,736,'Morro Agudo',70,'303',3531902),(32,704,'Araraquara',70,'048',3503208),(320,723,'Morungaba',70,'233',3532009),(321,702,'Murutinga do Sul',70,'083',3532108),(322,729,'Narandiba',70,'454',3532207),(323,740,'Natividade da Serra',70,'664',3532306),(324,710,'Nazaré Paulista',70,'194',3532405),(325,737,'Neves Paulista',70,'594',3532504),(326,742,'Nhandeara',70,'620',3532603),(327,737,'Nipoã',70,'595',3532702),(328,737,'Nova Aliança',70,'596',3532801),(329,704,'Nova Europa',70,'059',3532900),(33,724,'Araras',70,'236',3503307),(330,737,'Nova Granada',70,'597',3533007),(331,715,'Nova Guataporanga',70,'432',3533106),(332,702,'Nova Independência',70,'084',3533205),(333,703,'Nova Luzitânia',70,'111',3533304),(334,711,'Nova Odessa',70,'319',3533403),(335,713,'Novo Horizonte',70,'535',3533502),(336,736,'Nuporanga',70,'304',3533601),(337,726,'Ocauçu',70,'383',3533700),(338,727,'Óleo',70,'394',3533809),(339,707,'Olímpia',70,'133',3533908),(34,708,'Arealva',70,'144',3503406),(340,737,'Onda Verde',70,'598',3534005),(341,726,'Oriente',70,'384',3534104),(342,737,'Orindiúva',70,'599',3534203),(343,736,'Orlândia',70,'305',3534302),(344,681,'Osasco',70,'029',3534401),(345,726,'Oscar Bressane',70,'385',3534500),(346,701,'Osvaldo Cruz',70,'423',3534609),(347,727,'Ourinhos',70,'395',3534708),(348,715,'Ouro Verde',70,'433',3534807),(349,701,'Pacaembu',70,'424',3534906),(35,714,'Areias',70,'635',3503505),(350,737,'Palestina',70,'600',3535002),(351,713,'Palmares Paulista',70,'536',3535101),(352,721,'Palmeira d*Oeste',70,'565',3535200),(353,705,'Palmital',70,'368',3535309),(354,715,'Panorama',70,'434',3535408),(355,705,'Paraguaçu Paulista',70,'369',3535507),(356,738,'Paraibuna',70,'658',3535606),(357,713,'Paraíso',70,'537',3535705),(358,706,'Paranapanema',70,'685',3535804),(359,721,'Paranapuã',70,'566',3535903),(36,709,'Areiópolis',70,'693',3503604),(360,741,'Parapuã',70,'408',3536000),(361,709,'Pardinho',70,'699',3536109),(362,730,'Pariquera-Açu',70,'483',3536208),(363,717,'Patrocínio Paulista',70,'295',3536307),(364,715,'Paulicéia',70,'435',3536406),(365,711,'Paulínia',70,'320',3536505),(366,737,'Paulo de Faria',70,'601',3536604),(367,708,'Pederneiras',70,'156',3536703),(368,710,'Pedra Bela',70,'195',3536802),(369,716,'Pedranópolis',70,'552',3536901),(37,713,'Ariranha',70,'527',3503703),(370,717,'Pedregulho',70,'296',3537008),(371,711,'Pedreira',70,'321',3537107),(372,730,'Pedro de Toledo',70,'484',3537206),(373,703,'Penápolis',70,'112',3537305),(374,702,'Pereira Barreto',70,'085',3537404),(375,709,'Pereiras',70,'700',3537503),(376,733,'Peruíbe',70,'521',3537602),(377,703,'Piacatu',70,'113',3537701),(378,739,'Piedade',70,'747',3537800),(379,739,'Pilar do Sul',70,'748',3537909),(38,711,'Artur Nogueira',70,'309',3503802),(380,740,'Pindamonhangaba',70,'665',3538006),(381,713,'Pindorama',70,'538',3538105),(382,735,'Espírito Santo do Pinhal',70,'271',3515186),(383,710,'Pinhalzinho',70,'196',3538204),(384,729,'Piquerobi',70,'455',3538303),(385,718,'Piquete',70,'649',3538501),(386,710,'Piracaia',70,'197',3538600),(387,728,'Piracicaba',70,'250',3538709),(388,706,'Piraju',70,'686',3538808),(389,708,'Pirajuí',70,'157',3538907),(39,681,'Arujá',70,'006',3503901),(390,707,'Pirangi',70,'134',3539004),(391,681,'Pirapora do Bom Jesus',70,'030',3539103),(392,729,'Pirapozinho',70,'456',3539202),(393,724,'Pirassununga',70,'242',3539301),(394,708,'Piratininga',70,'158',3539400),(395,731,'Pitangueiras',70,'502',3539509),(396,737,'Planalto',70,'602',3539608),(397,705,'Platina',70,'371',3539707),(398,681,'Poá',70,'031',3539806),(399,737,'Poloni',70,'603',3539905),(4,735,'Águas da Prata',70,'267',3500402),(40,705,'Assis',70,'360',3504008),(400,726,'Pompéia',70,'386',3540002),(401,725,'Pongaí',70,'180',3540101),(402,731,'Pontal',70,'503',3540200),(403,742,'Pontes Gestal',70,'622',3540309),(404,716,'Populina',70,'553',3540408),(405,709,'Porangaba',70,'701',3540507),(406,739,'Porto Feliz',70,'749',3540606),(407,734,'Porto Ferreira',70,'070',3540705),(408,737,'Potirendaba',70,'604',3540804),(409,731,'Pradópolis',70,'504',3540903),(41,710,'Atibaia',70,'188',3504107),(410,733,'Praia Grande',70,'522',3541000),(411,708,'Presidente Alves',70,'159',3541109),(412,729,'Presidente Bernardes',70,'457',3541208),(413,729,'Presidente Epitácio',70,'458',3541307),(414,729,'Presidente Prudente',70,'459',3541406),(415,729,'Presidente Venceslau',70,'460',3541505),(416,725,'Promissão',70,'181',3541604),(417,741,'Quatá',70,'409',3541703),(418,741,'Queiroz',70,'410',3541802),(419,714,'Queluz',70,'639',3541901),(42,703,'Auriflama',70,'091',3504206),(420,741,'Quintana',70,'411',3542008),(421,728,'Rafard',70,'251',3542107),(422,729,'Rancharia',70,'461',3542206),(423,740,'Redenção da Serra',70,'666',3542305),(424,729,'Regente Feijó',70,'462',3542404),(425,708,'Reginópolis',70,'160',3542503),(426,730,'Registro',70,'485',3542602),(427,717,'Restinga',70,'297',3542701),(428,720,'Ribeira',70,'733',3542800),(429,734,'Ribeirão Bonito',70,'071',3542909),(43,708,'Avaí',70,'145',3504305),(430,720,'Ribeirão Branco',70,'734',3543006),(431,717,'Ribeirão Corrente',70,'298',3543105),(432,727,'Ribeirão do Sul',70,'396',3543204),(433,681,'Ribeirão Pires',70,'032',3543303),(434,731,'Ribeirão Preto',70,'505',3543402),(435,720,'Riversul',70,'736',3543501),(436,717,'Rifaina',70,'299',3543600),(437,704,'Rincão',70,'060',3543709),(438,741,'Rinópolis',70,'412',3543808),(439,732,'Rio Claro',70,'262',3543907),(44,703,'Avanhandava',70,'092',3504404),(440,728,'Rio das Pedras',70,'252',3544004),(441,681,'Rio Grande da Serra',70,'033',3544103),(442,742,'Riolândia',70,'623',3544202),(443,718,'Roseira',70,'651',3544301),(444,703,'Rubiácea',70,'114',3544400),(445,721,'Rubinéia',70,'568',3544509),(446,725,'Sabino',70,'182',3544608),(447,701,'Sagres',70,'426',3544707),(448,713,'Sales',70,'539',3544806),(449,736,'Sales Oliveira',70,'306',3544905),(45,706,'Avaré',70,'676',3504503),(450,681,'Salesópolis',70,'034',3545001),(451,701,'Salmourão',70,'427',3545100),(452,739,'Salto',70,'751',3545209),(453,739,'Salto de Pirapora',70,'750',3545308),(454,727,'Salto Grande',70,'397',3545407),(455,729,'Sandovalina',70,'465',3545506),(456,713,'Santa Adélia',70,'540',3545605),(457,721,'Santa Albertina',70,'569',3545704),(458,711,'Santa Bárbara d*Oeste',70,'322',3545803),(459,706,'Águas de Santa Bárbara',70,'674',3500550),(46,737,'Bady Bassitt',70,'581',3504602),(460,738,'Santa Branca',70,'659',3546009),(461,721,'Santa Clara d*Oeste',70,'570',3546108),(462,724,'Santa Cruz da Conceição',70,'243',3546207),(463,735,'Santa Cruz das Palmeiras',70,'274',3546306),(464,727,'Santa Cruz do Rio Pardo',70,'398',3546405),(465,704,'Santa Ernestina',70,'061',3546504),(466,721,'Santa Fé do Sul',70,'571',3546603),(467,732,'Santa Gertrudes',70,'263',3546702),(468,681,'Santa Isabel',70,'035',3546801),(469,704,'Santa Lucia',70,'062',3546900),(47,708,'Balbinos',70,'146',3504701),(470,728,'Santa Maria da Serra',70,'254',3547007),(471,715,'Santa Mercedes',70,'436',3547106),(472,721,'Santa Rita d*Oeste',70,'572',3547403),(473,734,'Santa Rita do Passa Quatro',70,'072',3547502),(474,731,'Santa Rosa do Viterbo',70,'507',3547601),(475,721,'Santana da Ponte Pensa',70,'574',3547205),(476,681,'Santana de Parnaíba',70,'036',3547304),(477,729,'Santo Anastácio',70,'466',3547700),(478,681,'Santo André',70,'037',3547809),(479,731,'Santo Antonio da Alegria',70,'508',3547908),(48,737,'Bálsamo',70,'582',3504800),(480,735,'Santo Antonio do Jardim',70,'275',3548104),(481,740,'Santo Antonio do Pinhal',70,'667',3548203),(482,711,'Santo Antonio de Posse',70,'323',3548005),(483,729,'Santo Expedito',70,'467',3548302),(484,703,'Santópolis do Aguapeí',70,'116',3548401),(485,733,'Santos',70,'523',3548500),(486,740,'São Bento do Sapucaí',70,'668',3548609),(487,681,'São Bernardo do Campo',70,'038',3548708),(488,681,'São Caetano do Sul',70,'039',3548807),(489,734,'São Carlos',70,'073',3548906),(49,714,'Bananal',70,'636',3504909),(490,721,'São Francisco',70,'575',3549003),(491,735,'São João da Boa Vista',70,'276',3549102),(492,716,'São João das Duas Pontes',70,'554',3549201),(493,715,'São João do Pau d*Alho',70,'437',3549300),(494,736,'São Joaquim da Barra',70,'307',3549409),(495,714,'São José do Barreiro',70,'640',3549607),(496,717,'São José da Bela Vista',70,'300',3549508),(497,735,'São José do Rio Pardo',70,'277',3549706),(498,737,'São José do Rio Preto',70,'605',3549805),(499,738,'São José dos Campos',70,'660',3549904),(5,710,'Águas de Lindóia',70,'186',3500501),(50,706,'Barão de Antonina',70,'677',3505005),(500,740,'São Luís do Paraitinga',70,'669',3550001),(501,709,'São Manuel',70,'703',3550100),(502,719,'São Miguel Arcanjo',70,'716',3550209),(503,681,'São Paulo',70,'041',3550308),(504,728,'São Pedro',70,'255',3550407),(505,727,'São Pedro do Turvo',70,'399',3550506),(506,739,'São Roque',70,'752',3550605),(507,712,'São Sebastião',70,'631',3550704),(508,735,'São Sebastião da Grama',70,'278',3550803),(509,731,'São Simão',70,'509',3550902),(51,703,'Barbosa',70,'093',3505104),(510,733,'São Vicente',70,'524',3551009),(511,719,'Sarapuí',70,'717',3551108),(512,706,'Sarutaiá',70,'687',3551207),(513,742,'Sebastianópolis do Sul',70,'624',3551306),(514,731,'Serra Azul',70,'510',3551405),(515,710,'Serra Negra',70,'198',3551603),(516,731,'Serrana',70,'511',3551504),(517,731,'Sertãozinho',70,'512',3551702),(518,730,'Sete Barras',70,'486',3551801),(519,707,'Severínia',70,'135',3551900),(52,722,'Bariri',70,'163',3505203),(520,714,'Silveiras',70,'641',3552007),(521,710,'Socorro',70,'199',3552106),(522,739,'Sorocaba',70,'753',3552205),(523,702,'Sud Mennucci',70,'086',3552304),(524,711,'Sumaré',70,'324',3552403),(525,681,'Suzano',70,'042',3552502),(526,713,'Tabapuã',70,'541',3552601),(527,704,'Tabatinga',70,'063',3552700),(528,681,'Taboão da Serra',70,'043',3552809),(529,729,'Taciba',70,'468',3552908),(53,722,'Barra Bonita',70,'164',3505302),(530,706,'Taguaí',70,'688',3553005),(531,707,'Taiaçu',70,'136',3553104),(532,707,'Taiúva',70,'137',3553203),(533,735,'Tambaú',70,'279',3553302),(534,737,'Tanabi',70,'606',3553401),(535,739,'Tapiraí',70,'754',3553500),(536,735,'Tapiratiba',70,'280',3553609),(537,704,'Taquaritinga',70,'064',3553708),(538,706,'Taquarituba',70,'689',3553807),(539,729,'Tarabaí',70,'469',3553906),(54,730,'Barra do Turvo',70,'473',3505401),(540,719,'Tatuí',70,'718',3554003),(541,740,'Taubaté',70,'670',3554102),(542,706,'Tejupá',70,'690',3554201),(543,729,'Teodoro Sampaio',70,'470',3554300),(544,707,'Terra Roxa',70,'138',3554409),(545,739,'Tietê',70,'755',3554508),(546,727,'Timburi',70,'400',3554607),(547,732,'Torrinha',70,'264',3554706),(548,740,'Tremembé',70,'671',3554805),(549,721,'Três Fronteiras',70,'576',3554904),(55,707,'Barretos',70,'123',3505500),(550,741,'Tupã',70,'413',3555000),(551,715,'Tupi Paulista',70,'438',3555109),(552,703,'Turiúba',70,'118',3555208),(553,716,'Turmalina',70,'555',3555307),(554,712,'Ubatuba',70,'632',3555406),(555,708,'Ubirajara',70,'161',3555505),(556,737,'Uchôa',70,'608',3555604),(557,737,'União Paulista',70,'609',3555703),(558,721,'Urânia',70,'577',3555802),(559,725,'Uru',70,'183',3555901),(56,731,'Barrinha',70,'490',3505609),(560,713,'Urupês',70,'542',3556008),(561,742,'Valentim Gentil',70,'625',3556107),(562,711,'Valinhos',70,'325',3556206),(563,703,'Valparaíso',70,'119',3556305),(564,735,'Vargem Grande do Sul',70,'281',3556404),(565,723,'Várzea Paulista',70,'234',3556503),(566,726,'Vera Cruz',70,'387',3556602),(567,711,'Vinhedo',70,'326',3556701),(568,707,'Viradouro',70,'139',3556800),(569,707,'Vista Alegre do Alto',70,'140',3556909),(57,681,'Barueri',70,'007',3505708),(570,739,'Votorantim',70,'756',3557006),(571,742,'Votuporanga',70,'626',3557105),(572,681,'Vargem Grande Paulista',70,'044',3556453),(573,719,'Alambari',70,'706',3500758),(574,739,'Alumínio',70,'739',3501152),(575,739,'Araçariguama',70,'740',3502754),(576,714,'Arapeí',70,'634',3503158),(577,721,'Aspásia',70,'558',3503950),(578,720,'Barra do Chapéu',70,'721',3505351),(579,733,'Bertioga',70,'516',3506359),(58,741,'Bastos',70,'403',3505807),(580,720,'Bom Sucesso de Itararé',70,'722',3507159),(581,708,'Borebi',70,'148',3507456),(582,730,'Cajati',70,'474',3509254),(583,719,'Campina do Monte Alegre',70,'709',3509452),(584,727,'Canitar',70,'390',3510153),(585,721,'Dirce Reis',70,'559',3513850),(586,713,'Elisiário',70,'530',3514924),(587,707,'Embaúba',70,'128',3514957),(588,729,'Emilianópolis',70,'445',3515129),(589,711,'Engenheiro Coelho',70,'312',3515152),(59,717,'Batatais',70,'285',3505906),(590,727,'Espírito Santo do Turvo',70,'392',3515194),(591,711,'Estiva Gerbi',70,'208',3557303),(592,729,'Euclides da Cunha Paulista',70,'447',3515350),(593,731,'Guatapará',70,'497',3518859),(594,711,'Holambra',70,'313',3519055),(595,711,'Hortolândia',70,'314',3519071),(596,706,'Iaras',70,'681',3519253),(597,730,'Ilha Comprida',70,'478',3520426),(598,702,'Ilha Solteira',70,'079',3520442),(599,720,'Itaóca',70,'728',3522158),(6,728,'Águas de São Pedro',70,'245',3500600),(60,708,'Bauru',70,'147',3506003),(600,720,'Itapirapuã Paulista',70,'730',3522653),(601,703,'Lourdes',70,'108',3527256),(602,713,'Marapoama',70,'533',3528858),(603,721,'Mesópolis',70,'563',3529658),(604,704,'Motuca',70,'058',3532058),(605,720,'Nova Campina',70,'732',3532827),(606,721,'Nova Canaã Paulista',70,'564',3532843),(607,713,'Novais',70,'534',3533254),(608,742,'Parisi',70,'621',3536257),(609,705,'Pedrinhas Paulista',70,'370',3537156),(61,707,'Bebedouro',70,'124',3506102),(610,721,'Pontalinda',70,'567',3540259),(611,718,'Potim',70,'650',3540754),(612,720,'Ribeirão Grande',70,'735',3543253),(613,729,'Rosana',70,'464',3544251),(614,728,'Saltinho',70,'253',3545159),(615,703,'São João de Iracema',70,'117',3549250),(616,681,'São Lourenço da Serra',70,'040',3549953),(617,703,'Santo Antonio do Aracanguá',70,'115',3548054),(618,702,'Suzanápolis',70,'087',3552551),(619,720,'Taquarivaí',70,'737',3553856),(62,703,'Bento de Abreu',70,'094',3506201),(620,705,'Tarumã',70,'372',3553955),(621,709,'Torre de Pedra',70,'704',3554656),(622,710,'Tuiuti',70,'200',3554953),(623,737,'Ubarana',70,'607',3555356),(624,710,'Vargem',70,'201',3556354),(625,737,'Zacarias',70,'610',3557154),(626,741,'Arco Íris',70,'402',3503356),(627,703,'Brejo Alegre',70,'098',3507753),(628,718,'Canas',70,'645',3509957),(629,701,'Pracinha',70,'425',3540853),(63,727,'Bernardino de Campos',70,'389',3506300),(630,709,'Pratânia',70,'702',3541059),(631,719,'Quadra',70,'715',3541653),(632,731,'Santa Cruz da Esperança',70,'506',3546256),(633,721,'Santa Salete',70,'573',3547650),(634,721,'Vitória Brasil',70,'578',3556958),(635,737,'Ipiguá',70,'587',3521150),(636,731,'Taquaral',70,'513',3553658),(637,726,'Fernão',70,'377',3515657),(638,704,'Gavião Peixoto',70,'054',3516853),(639,739,'Jumirim',70,'745',3525854),(64,703,'Bilac',70,'095',3506409),(640,729,'Nantes',70,'453',3532157),(641,703,'Nova Castilho',70,'110',3532868),(642,716,'Ouroeste',70,'551',3534757),(643,708,'Paulistânia',70,'155',3536570),(644,729,'Ribeirão dos Índios',70,'463',3543238),(645,704,'Trabiju',70,'065',3554755),(65,703,'Birigui',70,'096',3506508),(66,681,'Biritiba Mirim',70,'008',3506607),(67,704,'Boa Esperança do Sul',70,'049',3506706),(68,722,'Bocaina',70,'165',3506805),(680,1000,'Região Metropolitana da Baixada Santista',30,'002',680),(681,1000,'Região Metropolitana de São Paulo',30,'005',681),(682,1000,'Região Administrativa de Registro',10,'471',682),(683,1000,'Região Administrativa de Santos',10,'514',683),(684,1000,'Região Administrativa de São José dos Campos',10,'627',684),(685,1000,'Região Administrativa de Sorocaba',10,'672',685),(686,1000,'Região Administrativa de Campinas',10,'184',686),(687,1000,'Região Administrativa de Ribeirão Preto',10,'487',687),(688,1000,'Região Administrativa de Bauru',10,'141',688),(689,1000,'Região Administrativa de São José do Rio Preto',10,'525',689),(69,709,'Bofete',70,'694',3506904),(690,1000,'Região Administrativa de Araçatuba',10,'074',690),(691,1000,'Região Administrativa de Presidente Prudente',10,'414',691),(692,1000,'Região Administrativa de Marília',10,'358',692),(693,1000,'Região Administrativa Central',10,'045',693),(694,1000,'Região Administrativa de Barretos',10,'120',694),(695,1000,'Região Administrativa de Franca',10,'282',695),(7,708,'Agudos',70,'143',3500709),(70,719,'Boituva',70,'708',3507001),(701,691,'Região de Governo de Adamantina',20,'415',701),(702,690,'Região de Governo de Andradina',20,'075',702),(703,690,'Região de Governo de Araçatuba',20,'088',703),(704,693,'Região de Governo de Araraquara',20,'046',704),(705,692,'Região de Governo de Assis',20,'359',705),(706,685,'Região de Governo de Avaré',20,'673',706),(707,694,'Região de Governo de Barretos',20,'121',707),(708,688,'Região de Governo de Bauru',20,'142',708),(709,685,'Região de Governo de Botucatu',20,'691',709),(71,710,'Bom Jesus dos Perdões',70,'189',3507100),(710,686,'Região de Governo de Bragança Paulista',20,'185',710),(711,686,'Região de Governo de Campinas',20,'202',711),(712,684,'Região de Governo de Caraguatatuba',20,'628',712),(713,689,'Região de Governo de Catanduva',20,'526',713),(714,684,'Região de Governo de Cruzeiro',20,'633',714),(715,691,'Região de Governo de Dracena',20,'428',715),(716,689,'Região de Governo de Fernandópolis',20,'543',716),(717,695,'Região de Governo de Franca',20,'283',717),(718,684,'Região de Governo de Guaratinguetá',20,'642',718),(719,685,'Região de Governo de Itapetininga',20,'705',719),(72,741,'Borá',70,'404',3507209),(720,685,'Região de Governo de Itapeva',20,'719',720),(721,689,'Região de Governo de Jales',20,'556',721),(722,688,'Região de Governo de Jaú',20,'162',722),(723,686,'Região de Governo de Jundiaí',20,'225',723),(724,686,'Região de Governo de Limeira',20,'235',724),(725,688,'Região de Governo de Lins',20,'173',725),(726,692,'Região de Governo de Marília',20,'373',726),(727,692,'Região de Governo de Ourinhos',20,'388',727),(728,686,'Região de Governo de Piracicaba',20,'244',728),(729,691,'Região de Governo de Presidente Prudente',20,'439',729),(73,722,'Boracéia',70,'166',3507308),(730,682,'Região de Governo de Registro',20,'472',730),(731,687,'Região de Governo de Ribeirão Preto',20,'488',731),(732,686,'Região de Governo de Rio Claro',20,'256',732),(733,683,'Região de Governo de Santos',20,'515',733),(734,693,'Região de Governo de São Carlos',20,'066',734),(735,686,'Região de Governo de São João da Boa Vista',20,'265',735),(736,695,'Região de Governo de São Joaquim da Barra',20,'301',736),(737,689,'Região de Governo de São José do Rio Preto',20,'579',737),(738,684,'Região de Governo de São José dos Campos',20,'652',738),(739,685,'Região de Governo de Sorocaba',20,'738',739),(74,704,'Borborema',70,'050',3507407),(740,684,'Região de Governo de Taubaté',20,'661',740),(741,692,'Região de Governo de Tupã',20,'401',741),(742,689,'Região de Governo de Votuporanga',20,'611',742),(75,709,'Botucatu',70,'695',3507506),(76,710,'Bragança Paulista',70,'190',3507605),(77,703,'Braúna',70,'097',3507704),(78,731,'Brodowski',70,'491',3507803),(79,732,'Brotas',70,'258',3507902),(8,729,'Alfredo Marcondes',70,'440',3500808),(80,720,'Buri',70,'723',3508009),(81,703,'Buritama',70,'099',3508108),(82,717,'Buritizal',70,'286',3508207),(83,708,'Cabrália Paulista',70,'149',3508306),(84,723,'Cabreúva',70,'226',3508405),(85,738,'Caçapava',70,'653',3508504),(86,718,'Cachoeira Paulista',70,'644',3508603),(87,735,'Caconde',70,'268',3508702),(88,725,'Cafelândia',70,'174',3508801),(89,729,'Caiabu',70,'443',3508900),(9,707,'Altair',70,'122',3500907),(90,681,'Caieiras',70,'009',3509007),(91,729,'Caiuá',70,'444',3509106),(92,681,'Cajamar',70,'010',3509205),(93,707,'Cajobi',70,'125',3509304),(94,731,'Cajuru',70,'492',3509403),(95,711,'Campinas',70,'310',3509502),(96,723,'Campo Limpo Paulista',70,'227',3509601),(97,740,'Campos do Jordão',70,'662',3509700),(98,705,'Campos Novos Paulista',70,'361',3509809),(99,730,'Cananéia',70,'475',3509908),(696,1000,'Região Metropolitana de Campinas',30,'003',696),(486,697,'São Bento do Sapucaí',99,'668',3548609),(500,697,'São Luís do Paraitinga',99,'669',3550001),(541,697,'Taubaté',99,'670',3554102),(548,697,'Tremembé',99,'671',3554805),(481,697,'Santo Antonio do Pinhal',99,'667',3548203),(423,697,'Redenção da Serra',99,'666',3542305),(380,697,'Pindamonhangaba',99,'665',3538006),(323,697,'Natividade da Serra',99,'664',3532306),(263,697,'Lagoinha',99,'663',3526308),(97,697,'Campos do Jordão',99,'662',3509700),(499,697,'São José dos Campos',99,'660',3549904),(460,697,'Santa Branca',99,'659',3546009),(356,697,'Paraibuna',99,'658',3535606),(317,697,'Monteiro Lobato',99,'657',3531704),(249,697,'Jambeiro',99,'656',3524907),(244,697,'Jacareí',99,'655',3524402),(202,697,'Igaratá',99,'654',3520202),(85,697,'Caçapava',99,'653',3508504),(554,697,'Ubatuba',99,'632',3555406),(507,697,'São Sebastião',99,'631',3550704),(204,697,'Ilhabela',99,'630',3520400),(105,697,'Caraguatatuba',99,'629',3510500),(628,697,'Canas',99,'645',3509957),(611,697,'Potim',99,'650',3540754),(443,697,'Roseira',99,'651',3544301),(385,697,'Piquete',99,'649',3538501),(272,697,'Lorena',99,'648',3527207),(184,697,'Guaratinguetá',99,'647',3518404),(136,697,'Cunha',99,'646',3513603),(86,697,'Cachoeira Paulista',99,'644',3508603),(25,697,'Aparecida',99,'643',3502507),(576,697,'Arapeí',99,'634',3503158),(520,697,'Silveiras',99,'641',3552007),(495,697,'São José do Barreiro',99,'640',3549607),(419,697,'Queluz',99,'639',3541901),(266,697,'Lavrinhas',99,'638',3526605),(134,697,'Cruzeiro',99,'637',3513405),(49,697,'Bananal',99,'636',3504909),(35,697,'Areias',99,'635',3503505),(697,1000,'Região Metropolitana do Vale do Paraíba e Litoral Norte',30,'004',697),(524,696,'Sumaré',99,'324',3552403),(482,696,'Santo Antonio de Posse',99,'323',3548005),(458,696,'Santa Bárbara d*Oeste',99,'322',3545803),(371,696,'Pedreira',99,'321',3537107),(365,696,'Paulínia',99,'320',3536505),(334,696,'Nova Odessa',99,'319',3533403),(318,696,'Monte Mor',99,'318',3531803),(247,696,'Jaguariúna',99,'317',3524709),(205,696,'Indaiatuba',99,'315',3520509),(128,696,'Cosmópolis',99,'311',3512803),(95,696,'Campinas',99,'310',3509502),(38,696,'Artur Nogueira',99,'309',3503802),(16,696,'Americana',99,'308',3501608),(562,696,'Valinhos',99,'325',3556206),(567,696,'Vinhedo',99,'326',3556701),(589,696,'Engenheiro Coelho',99,'312',3515152),(594,696,'Holambra',99,'313',3519055),(595,696,'Hortolândia',99,'314',3519071),(234,696,'Itatiba',99,'316',3523404),(135,680,'Cubatão',99,'517',3513504),(187,680,'Guarujá',99,'518',3518701),(221,680,'Itanhaém',99,'519',3522109),(311,680,'Mongaguá',99,'520',3531100),(376,680,'Peruíbe',99,'521',3537602),(410,680,'Praia Grande',99,'522',3541000),(485,680,'Santos',99,'523',3548500),(510,680,'São Vicente',99,'524',3551009),(579,680,'Bertioga',99,'516',3506359);
                INSERT INTO " . $wpdb->prefix . "tb_unidade
                    VALUES (0,' '),(1,'Em %'),(2, 'Base: 2002=100');
                INSERT INTO " . $wpdb->prefix . "tb_grafico
                    VALUES (1,'Taxa de crescimento mensal do PIB em relação ao mês imediatamente anterior com ajuste sazonal','','Line','S',1),(2,'Taxa de crescimento mensal do PIB em relação ao mesmo mês do ano anterior','','Line','S',1),(3,'Taxa de crescimento do PIB acumulada no ano','','Column','S',1),(4,'Taxa de crescimento do PIB acumulada nos 12 meses','','Line','S',1),(5,'Taxa de crescimento mensal do PIB e VA por setores em relação ao mês imediatamente anterior, com ajuste sazonal','','Bar','N',1),(6,'Tendência do índice mensal do PIB','','Line','S',2);
                INSERT INTO " . $wpdb->prefix . "tb_variavel
                    VALUES (103,'Taxa de ajuste mensal do PIB - mar',1,3),(201,'Taxa de crescimento mensal do PIB em relação ao mesmo mes do ano anterior - jan',1,1),(301,'Taxa de crescimento mensal do PIB - acumulado no ano - jan',1,1),(401,'Taxa de crescimento mensal do PIB - acumulado 12 meses - jan',1,1),(601,'Tendência do índice mensal do PIB - jan',0,1),(104,'Taxa de ajuste mensal do PIB - abr',1,4),(102,'Taxa de ajuste mensal do PIB - fev',1,2),(101,'Taxa de ajuste mensal do PIB - jan',1,1),(106,'Taxa de ajuste mensal do PIB - jun',1,6),(107,'Taxa de ajuste mensal do PIB - jul',1,7),(108,'Taxa de ajuste mensal do PIB - ago',1,8),(109,'Taxa de ajuste mensal do PIB - set',1,9),(110,'Taxa de ajuste mensal do PIB - out',1,10),(111,'Taxa de ajuste mensal do PIB - nov',1,11),(112,'Taxa de ajuste mensal do PIB - dez',1,12),(202,'Taxa de crescimento mensal do PIB em relação ao mesmo mes do ano anterior - fev',1,2),(203,'Taxa de crescimento mensal do PIB em relação ao mesmo mes do ano anterior - mar',1,3),(204,'Taxa de crescimento mensal do PIB em relação ao mesmo mes do ano anterior - abr',1,4),(205,'Taxa de crescimento mensal do PIB em relação ao mesmo mes do ano anterior - mai',1,5),(206,'Taxa de crescimento mensal do PIB em relação ao mesmo mes do ano anterior - jun',1,6),(207,'Taxa de crescimento mensal do PIB em relação ao mesmo mes do ano anterior - jul',1,7),(208,'Taxa de crescimento mensal do PIB em relação ao mesmo mes do ano anterior - ago',1,8),(209,'Taxa de crescimento mensal do PIB em relação ao mesmo mes do ano anterior - set',1,9),(210,'Taxa de crescimento mensal do PIB em relação ao mesmo mes do ano anterior - out',1,10),(211,'Taxa de crescimento mensal do PIB em relação ao mesmo mes do ano anterior - nov',1,11),(212,'Taxa de crescimento mensal do PIB em relação ao mesmo mes do ano anterior - dez',1,12),(302,'Taxa de crescimento mensal do PIB - acumulado no ano - fev',1,2),(303,'Taxa de crescimento mensal do PIB - acumulado no ano - mar',1,3),(304,'Taxa de crescimento mensal do PIB - acumulado no ano - abr',1,4),(305,'Taxa de crescimento mensal do PIB - acumulado no ano - mai',1,5),(306,'Taxa de crescimento mensal do PIB - acumulado no ano - jun',1,6),(307,'Taxa de crescimento mensal do PIB - acumulado no ano - jul',1,7),(308,'Taxa de crescimento mensal do PIB - acumulado no ano - ago',1,8),(309,'Taxa de crescimento mensal do PIB - acumulado no ano - set',1,9),(310,'Taxa de crescimento mensal do PIB - acumulado no ano - out',1,10),(311,'Taxa de crescimento mensal do PIB - acumulado no ano - nov',1,11),(312,'Taxa de crescimento mensal do PIB - acumulado no ano - dez',1,12),(402,'Taxa de crescimento mensal do PIB - acumulado 12 meses - fev',1,2),(403,'Taxa de crescimento mensal do PIB - acumulado 12 meses - mar',1,3),(404,'Taxa de crescimento mensal do PIB - acumulado 12 meses - abr',1,4),(405,'Taxa de crescimento mensal do PIB - acumulado 12 meses - mai',1,5),(406,'Taxa de crescimento mensal do PIB - acumulado 12 meses - jun',1,6),(407,'Taxa de crescimento mensal do PIB - acumulado 12 meses - jul',1,7),(408,'Taxa de crescimento mensal do PIB - acumulado 12 meses - ago',1,8),(409,'Taxa de crescimento mensal do PIB - acumulado 12 meses - set',1,9),(410,'Taxa de crescimento mensal do PIB - acumulado 12 meses - out',1,10),(411,'Taxa de crescimento mensal do PIB - acumulado 12 meses - nov',1,11),(412,'Taxa de crescimento mensal do PIB - acumulado 12 meses - dez',1,12),(602,'Tendência do índice mensal do PIB - fev',0,2),(603,'Tendência do índice mensal do PIB - mar',0,3),(604,'Tendência do índice mensal do PIB - abr',0,4),(605,'Tendência do índice mensal do PIB - mai',0,5),(606,'Tendência do índice mensal do PIB - jun',0,6),(607,'Tendência do índice mensal do PIB - jul',0,7),(608,'Tendência do índice mensal do PIB - ago',0,8),(609,'Tendência do índice mensal do PIB - set',0,9),(610,'Tendência do índice mensal do PIB - out',0,10),(611,'Tendência do índice mensal do PIB - nov',0,11),(501,'Taxa de Crescimento mensal do PIB - Agropecuária',1,13),(502,'Taxa de Crescimento mensal do PIB - Indústria',1,14),(507,'Taxa de Crescimento mensal do PIB - Serviços',1,19),(511,'Taxa de Crescimento mensal do PIB - VA',1,23),(105,'Taxa de ajuste mensal do PIB - mai',1,5),(513,'Taxa de crescimento mensal do PIB - PIB',1,27),(612,'Tendência do índice mensal do PIB - dez',0,12);
                INSERT INTO " . $wpdb->prefix . "tb_variavel_grafico
                    VALUES (103,1,'3',1,3),(201,2,'1',1,1),(301,3,'1',1,1),(401,4,'1',1,1),(601,6,'1',0,1),(104,1,'4',1,4),(102,1,'2',1,2),(101,1,'1',1,1),(106,1,'6',1,6),(107,1,'7',1,7),(108,1,'8',1,8),(109,1,'9',1,9),(110,1,'10',1,10),(111,1,'11',1,11),(112,1,'12',1,12),(202,2,'2',1,2),(203,2,'3',1,3),(204,2,'4',1,4),(205,2,'5',1,5),(206,2,'6',1,6),(207,2,'7',1,7),(208,2,'8',1,8),(209,2,'9',1,9),(210,2,'10',1,10),(211,2,'11',1,11),(212,2,'12',1,12),(302,3,'2',1,2),(303,3,'3',1,3),(304,3,'4',1,4),(305,3,'5',1,5),(306,3,'6',1,6),(307,3,'7',1,7),(308,3,'8',1,8),(309,3,'9',1,9),(310,3,'10',1,10),(311,3,'11',1,11),(312,3,'12',1,12),(402,4,'2',1,2),(403,4,'3',1,3),(404,4,'4',1,4),(405,4,'5',1,5),(406,4,'6',1,6),(407,4,'7',1,7),(408,4,'8',1,8),(409,4,'9',1,9),(410,4,'10',1,10),(411,4,'11',1,11),(412,4,'12',1,12),(602,6,'2',0,2),(603,6,'3',0,3),(604,6,'4',0,4),(605,6,'5',0,5),(606,6,'6',0,6),(607,6,'7',0,7),(608,6,'8',0,8),(609,6,'9',0,9),(610,6,'10',0,10),(611,6,'11',0,11),(501,5,'1',1,13),(502,5,'2',1,14),(507,5,'3',1,19),(511,5,'4',1,23),(105,1,'5',1,5),(513,5,'5',1,27),(612,6,'12',0,12);
            ";

            require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
            dbDelta( $sql );
        }

        /**
	 * Registers and enqueues stylesheets for the administration panel and the
	 * public facing site.
	 */
	private function register_scripts_and_styles() {
	    wp_register_script( self::slug . '-jsapi-script', 'https://www.google.com/jsapi');
	    wp_enqueue_script( self::slug . '-jsapi-script');
	    
	} // end register_scripts_and_styles

        /**
	 * Helper function for registering and enqueueing scripts and styles.
	 *
	 * @name	The 	ID to register with WordPress
	 * @file_path		The path to the actual file
	 * @is_script		Optional argument for if the incoming file_path is a JavaScript source file.
	 */
	private function load_file( $name, $file_path, $is_script = false ) {

		$url = plugins_url($file_path, __FILE__);
		$file = plugin_dir_path(__FILE__) . $file_path;

		if( file_exists( $file ) ) {
			if( $is_script ) {
				wp_register_script( $name, $url, array('jquery') ); //depends on jquery
				wp_enqueue_script( $name );
			} else {
				wp_register_style( $name, $url );
				wp_enqueue_style( $name );
			} // end if
		} // end if

	} // end load_file

        function render_graph($atts, $content = null){
            $id = $atts['id'];
            $arq = (isset($atts['arq'])?$atts['arq']:'bd');
            $width = (isset($atts['width'])?$atts['width']:'600');
            $height = (isset($atts['height'])?$atts['height']:'400');
            $w = (isset($atts['w'])?$atts['w']:'');
            $h = (isset($atts['h'])?$atts['h']:'');
            $float = (isset($atts['float'])?$atts['float']:'none');
            $show_loc = (isset($atts['show_loc'])?$atts['show_loc']:true);
            $location = (isset($atts['location'])?explode('|', $atts['location']):false);
            //$ = (isset($atts[''])?$atts['']:'');

            $graph = monta_graph(array('id'=>$id, 'arq'=>$arq));
            
            $uniqid = uniqid();

            $html = "<div id='graph_{$id}_{$arq}' style='width:$width;height:$height;float:$float;'>";
            $script = "<script>
            google.load(\"visualization\", \"1\", {packages:[\"corechart\"],'callback': 'drawVisualization_{$id}_{$arq}_$uniqid()'});
            google.setOnLoadCallback(function() {
                drawVisualization_{$id}_{$arq}_$uniqid();
            });

            function drawVisualization_{$id}_{$arq}_$uniqid(){
            ";

            foreach ($graph['localidades'] as $id_loc => $loc){
                if(!$location || $location == $id_loc || in_array($id_loc, $location)){
                    $html .= "<div id='graph_{$id}_{$arq}_{$id_loc}'></div>";
                    $script .= "\r\n var data = new google.visualization.DataTable();\r\n";
                    foreach($graph[$id_loc]['cols'] as $col){
                        $script .= "data.addColumn('{$col['type']}', '{$col['name']}');\r\n";
                    }
                    foreach($graph['vars'] as $v){
                        $script .= "var linha = ['{$v->label}'];\r\n";
                        foreach($graph[$id_loc]['cols'] as $col){
                            if($col['type'] == 'number'){
                                if(isset($graph[$id_loc][$v->id]) && isset($graph[$id_loc][$v->id][$col['name']]))
                                    $script .= "linha.push(parseFloat(".str_replace(",", ".", $graph[$id_loc][$v->id][$col['name']]->valor)."));\r\n";
                                else
                                    $script .= "linha.push(null);\r\n";
                            }
                        }
                        $script .= "data.addRow(linha);\r\n";
                    }
                }
                $is3d = 'false';
                switch($graph['graph']->tipo){
                    case 'Line':
                        $script .= "var chart = new google.visualization.LineChart(document.getElementById(\"graph_{$id}_{$arq}_{$id_loc}\"));\r\n";
                        break;
                    case 'Column':
                        $script .= "var chart = new google.visualization.ColumnChart(document.getElementById(\"graph_{$id}_{$arq}_{$id_loc}\"));\r\n";
                        break;
                    case 'Bar':
                        $script .= "var chart = new google.visualization.BarChart(document.getElementById(\"graph_{$id}_{$arq}_{$id_loc}\"));\r\n";
                        break;
                    case 'Line3D':
                        $script .= "var chart = new google.visualization.LineChart(document.getElementById(\"graph_{$id}_{$arq}_{$id_loc}\"));\r\n";
                        $is3d = 'true';
                        break;
                    case 'Column3D':
                        $script .= "var chart = new google.visualization.ColumnChart(document.getElementById(\"graph_{$id}_{$arq}_{$id_loc}\"));\r\n";
                        $is3d = 'true';
                        break;
                    case 'Bar3D':
                        $script .= "var chart = new google.visualization.BarChart(document.getElementById(\"graph_{$id}_{$arq}_{$id_loc}\"));\r\n";
                        $is3d = 'true';
                        break;
                }
                if($show_loc){
                    $title = "\"{$loc}\" + \"\\r\\n\" + \"{$graph['graph']->nome}\"";
                }else{
                    $title = "\"{$graph['graph']->nome}\"";
                }
                $script .= "var options = {'title':{$title},
                               'width':$width,
                               'height':$height,
                               'is3D':$is3d
                };\r\n";

                $script .= "if(jQuery.type(chart) !== 'undefined') chart.draw(data, options);\r\n";
            }
            $html .= "</div>";
            $script .= "}\r\n</script>";
            
            $ret = $html . $script;

            return $ret;
        }

        function seadepibgraph_add_quicktags() {
            if (wp_script_is('quicktags')){
            ?>
                <script type="text/javascript">
                QTags.addButton( 'eg_Graph', 'Inserir Gráfico', insert_graph, null, 'g', 'Insere um gráfico', 200 );
                function insert_graph(){
                    jQuery.ajax({
                        url: 'admin-post.php?action=ins_graph_show',
                        type: 'POST',
                        dataType : 'json'
                    }).done(function(dados){
                        jQuery('#ins_graph').html(dados.html).show();
                    });
                }
                jQuery('html').delegate('.get_graph_from', 'click', function(e){
                    var tipo = jQuery(this).attr('id');
                    jQuery('#btn_graph_ok').attr('disabled', true);
                    jQuery('#inc_graph').html("<div style='width:100%;text-align:center;margin-top:20%;font-size:2em;'>Carregando...</div>");
                    switch(tipo){
                        case "get_graph_from_lista":
                            var html = get_lista_graph();
                            break;
                        case "get_graph_from_monta":
                            var html = get_monta_graph();
                            break;
                    }
                });
                jQuery('html').delegate('#btn_graph_cancel', 'click', function(e){
                    jQuery('#ins_graph').html("").hide();
                });
                jQuery('html').delegate('#btn_graph_ok', 'click', function(e){
                    var tipo = jQuery('.get_graph_from:checked').attr('id');
                    var curr = jQuery('#content').val();
                    switch(tipo){
                        case "get_graph_from_lista":
                            graph = jQuery('#sel_lista').val();
                            break;
                        case "get_graph_from_monta":
                            graph = jQuery('#graph_code').val();
                            break;
                    }
                    jQuery('#content').val(curr + "\r\n" + graph);
                    jQuery('#ins_graph').html("").hide();
                });
                jQuery('html').delegate('#sel_lista', 'change', function(e){
                    var codigo = jQuery(this).val();
                    jQuery('#graph_preview').html("<div style='padding-top:30%;width:100%;text-align:center;'>Carregando preview...</div>");
                    jQuery('#btn_graph_ok').attr('disabled', false);
                    jQuery.ajax({
                        url: 'admin-post.php?action=preview_graph',
                        data: 'codigo='+codigo,
                        type: 'POST',
                        dataType : 'html'
                    }).done(function(dados){
                        jQuery('#graph_preview').html(dados);
                    });
                });
                jQuery('html').delegate('#sel_arq', 'change', function(e){
                    var sel_arq = jQuery(this).val();
                    set_atts();
                    jQuery('#loc_list').html("Carregando lista de localidades...");
                    jQuery.ajax({
                        url: 'admin-post.php?action=lista_localidades',
                        data: 'id_arq='+sel_arq,
                        type: 'POST',
                        dataType : 'json'
                    }).done(function(dados){
                        var html = "";
                        jQuery.each(dados, function(i, dado){
                            html += "<input id='id_loc' type='checkbox' value='"+dado.id_loc+"'> " + dado.nome + "<br/>";
                        });
                        jQuery('#loc_list').html(html);
                    });
                });
                jQuery('html').delegate('#sel_conf, #show_loc, #width, #height', 'change', function(e){
                    set_atts();
                });
                jQuery('html').delegate('#id_loc', 'click', function(e){
                    set_atts();
                });
                jQuery('html').delegate('#btn_align_left', 'click', function(e){
                    jQuery('#align').val('left');
                    set_atts();
                });
                jQuery('html').delegate('#btn_align_center', 'click', function(e){
                    jQuery('#align').val('');
                    set_atts();
                });
                jQuery('html').delegate('#btn_align_right', 'click', function(e){
                    jQuery('#align').val('right');
                    set_atts();
                });
                jQuery('html').delegate('#btn_preview', 'click', function(e){
                    if(jQuery('#sel_conf').val() == ''){
                        alert('Selecione um gráfico.');
                        return false;
                    }
                    var codigo = jQuery('#graph_code').val();
                    jQuery('#graph_preview').html("<div style='padding-top:30%;width:100%;text-align:center;'>Carregando preview...</div>");
                    jQuery.ajax({
                        url: 'admin-post.php?action=preview_graph',
                        data: 'codigo='+codigo,
                        type: 'POST',
                        dataType : 'html'
                    }).done(function(dados){
                        jQuery('#graph_preview').html(dados);
                    });
                });

                function get_lista_graph(){
                    jQuery.ajax({
                        url: 'admin-post.php?action=get_lista_graph',
                        type: 'POST',
                        dataType : 'json'
                    }).done(function(dados){
                        if(dados.length == 0){
                            var html = "<div style='width:100%;text-align:center;margin-top:20%;font-size:2em;'>Não há gráficos na lista!</div>";
                            jQuery('#inc_graph').html(html);
                            return;
                        }
                        //console.log(dados);
                        var html = "<select id='sel_lista' style='width:100%;'>";
                            html += "<option value=''>Selecione...</option>";
                        jQuery.each(dados, function(i, lista){
                            html += "<option>"+lista.codigo+"</option>";
                        });
                        html += "</select>";

                        html += "<div id='graph_preview' style='border:1px black solid;width:400px;height:300px;right:50%;margin-right:-200px;position:absolute;margin-top:30px;'><div style='padding-top:30%;width:100%;text-align:center;'>Preview</div></div>";

                        jQuery('#inc_graph').html(html);
                        return;
                    });
                }
                function get_monta_graph(){
                    jQuery.ajax({
                        url: 'admin-post.php?action=get_monta_graph',
                        type: 'POST',
                        dataType : 'json'
                    }).done(function(dados){
                        //console.log(dados);
                        var html = "<input id='graph_code' value='' placeholder='Código do gráfico...' style='width:79%;' readonly>";
                        html += "<input type='button' value='Salvar na lista' id='btn_save_code' style='width:20%;'>";
                        html += "<select id='sel_arq' style='width:35%;'><option value=''>Selecione o arquivo...</option><option value='bd'>Todos os dados</option></select>";
                        html += "<select id='sel_conf' style='width:64%;'><option value=''>Selecione o gráfico...</option></select>";
                        html += "<div style='float:left;'>";
                        html += "<label for='width' style='width:50px;display:inline-block;'>Largura:</label> <input type='number' id='width' value='600' style='width:100px;'><br>";
                        html += "<label for='height' style='width:50px;display:inline-block;'>Altura:</label> <input type='number' id='height' value='400' style='width:100px;'>";
                        //html += "<button id='btn_align_left' class='btn-align' title='Alinhar à esquerda do texto'><span class='dashicons dashicons-align-left'></button>";
                        //html += "<button id='btn_align_center' class='btn-align' title='Sem alinhamento'><span class='dashicons dashicons-align-center'></button>";
                        //html += "<button id='btn_align_right' class='btn-align' title='Alinhar à direita do texto'><span class='dashicons dashicons-align-right'></button>";
                        html += "<input type='hidden' id='align' value=''>";
                        html += "<br><input type='checkbox' id='show_loc'> <label for='show_loc'>Não mostrar nome(s) da(s) localidade(s)</label>";
                        html += "<br>Localidades:";
                        html += "<div id='loc_list' style='width:200px; border: 1px solid black; max-height: 300px; overflow-y:auto; padding: 10px;'>";
                        html += "Selecione um arquivo para determinar as localidades disponíveis";
                        html += "</div>";
                        html += "</div>";
                        html += "<div id='graph_preview' style='float: right;border: 1px black solid;width: 400px;height: 300px;margin-right: 10px;margin-top: 30px;'><div style='padding-top:30%;width:100%;text-align:center;'>Preview</div></div>";
                        html += "<button id='btn_preview' style='float: right;width: 402px;margin-right: 10px;'>Vizualizar preview</button>";
                        jQuery('#inc_graph').html(html);
                        jQuery('#sel_arq').append(dados.lista_arq);
                        jQuery('#sel_conf').append(dados.lista_conf);
                    });
                }
                function set_atts(){
                    var arq = jQuery('#sel_arq').val();
                    var conf = jQuery('#sel_conf').val();
                    var float = jQuery('#align').val();
                    var show_loc = jQuery('#show_loc').is(":checked");
                    var width = jQuery('#width').val();
                    var height = jQuery('#height').val();
                    
                    if(jQuery('#id_loc').length > 0){
                        var location = []
                        jQuery('#id_loc:checked').each(function(){
                            location.push(jQuery(this).val());
                        });
                        location = location.join('|');
                    }else{
                        var location = '';
                    }
                    
                    var code = "[graph ";

                    if(arq != '') code += 'arq="'+arq+'" ';
                    if(conf != ''){
                        code += 'id="'+conf+'" ';
                        jQuery('#btn_graph_ok').attr('disabled', false);
                    }else{
                        jQuery('#btn_graph_ok').attr('disabled', true);
                    }
                    if(width != '600' && width != '') code += 'width="'+width+'" ';
                    if(height != '400' && height != '') code += 'height="'+height+'" ';
                    if(float != '') code += 'float="'+float+'" ';
                    if(location != '') code += 'location="'+location+'" ';
                    if(show_loc) code += 'show_loc="0" ';

                    code += "]";
                    jQuery('#graph_code').val(code);
                }
                </script>
                <div id="ins_graph" style="width:770px;height:570px;position:fixed;background:#fff;;top:50%;left:50%;margin-top:-300px;margin-left:-400px;z-index:1000;border:1px solid grey;display:none; padding: 15px;"></div>
            <?php
            }
        }

        function ins_graph_show(){
            $dados = array('success'=>true);
            $dados['html'] = "
                <h1 style='text-align:center;'>Incluír Gráfico</h1>
                <table width='50%' style='margin-left:25%;text-align:center;'>
                    <tr>
                        <td width='50%'><input type='radio' id='get_graph_from_lista' class='get_graph_from' name='get_graph_from' value='lista'><label for='get_graph_from_lista'>Escolher da lista</label></td>
                        <td width='50%'><input type='radio' id='get_graph_from_monta' class='get_graph_from' name='get_graph_from' value='new'><label for='get_graph_from_monta'>Montar</label></td>
                    </tr>
                </table>
                <div id='inc_graph' style='padding:25px;'></div>
                <div style='position:absolute; bottom:0px; width:100%; padding:10px; text-align:center;'>
                    <input type='button' id='btn_graph_cancel' value='Cancelar'>
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    <input type='button' id='btn_graph_ok' value='OK' disabled>
                </div>
            ";
            echo json_encode($dados);
            return;
        }

        function get_lista_graph(){
            global $wpdb;

            $table_name = $wpdb->prefix . 'tb_lista_graph';
            $lista = $wpdb->get_results("SELECT * FROM $table_name ORDER BY id DESC LIMIT 15 ");

            echo json_encode($lista);
        }

        function get_monta_graph(){
            $dados['lista_arq'] = lista_arq();
            $dados['lista_conf'] = lista_conf();

            echo json_encode($dados);
        }

        function ins_lista_graph(){
            global $wpdb;

            $codigo = stripslashes($_POST['codigo']);

            $table_name = $wpdb->prefix . 'tb_lista_graph';
            $last = $wpdb->get_results("SELECT * FROM $table_name ORDER BY id DESC LIMIT 5 ");

            foreach($last as $lastcode){
                if($codigo == $lastcode->codigo){
                    echo json_encode(array('success'=>false, 'error'=>'duplicado'));
                    return;
                }
            }
            
            $dados = array('codigo'=>$codigo);
            
            if($wpdb->insert( $table_name, $dados )){
                echo json_encode(array('success'=>true));
            }else{
                echo json_encode(array('success'=>false, 'error'=>'bd'));
            }

            return;
        }
        
        function preview_graph(){
            $codigo = stripslashes($_POST['codigo']);

            $codigo = str_replace('[graph ', '', $codigo);
            $codigo = str_replace(']', '', $codigo);
            $codigo = str_replace('"', '', $codigo);

            $codigo = explode(' ', $codigo);

            $atts = array();
            foreach($codigo as $at){
                $at = explode('=', $at);
                $atts[$at[0]] = $at[1];
            }
            $atts['width'] = '400';
            $atts['height'] = '300';

            echo $this->render_graph($atts);
            return;
        }

        function lista_localidades(){
            global $wpdb;

            $id_arq = $_POST['id_arq'];

            $table_dados = $wpdb->prefix . 'dados_graph';
            $table_loc   = $wpdb->prefix . 'tb_localidade';
            
            $sql = "SELECT distinct d.id_loc, l.nome
                   FROM $table_loc l
                   JOIN $table_dados d ON d.id_loc = l.id ";
            if($id_arq != 'bd') $sql .= "WHERE d.id_csv_arq = $id_arq";
            $sql .= "ORDER BY l.ordem";
            
            $list_loc = $wpdb->get_results($sql);
            
            echo json_encode($list_loc);
        }
} // end class
new SeadePibGraph();

//Action do upload do csv
add_action( 'admin_post_csv_upload', 'csv_upload' );
function csv_upload(){
    if(!isset($_FILES['csv_arq']) || $_FILES['csv_arq']['error']){
        $json = array('success' => false, 'error' => $_FILES['csv_arq']['error']);
        if(isset($_FILES['csv_arq']['error'])){
            switch ($_FILES['csv_arq']['error']) {
                case UPLOAD_ERR_INI_SIZE:
                case UPLOAD_ERR_FORM_SIZE:
                    $json['error_msg'] = "O arquivo no upload é maior do que o limite.";
                    break;
                case UPLOAD_ERR_PARTIAL:
                    $json['error_msg'] = "O upload do arquivo foi feito parcialmente.";
                    break;
                case UPLOAD_ERR_NO_FILE:
                    $json['error_msg'] = "Não foi feito o upload do arquivo.";
                    break;
            }
        }
        
        echo json_encode($json);
        die();
    }
    $tmp_name    = $_FILES['csv_arq']['tmp_name'];
    $file['name'] = $_FILES['csv_arq']['name'];
    $file['type'] = $_FILES['csv_arq']['type'];
    $file['size'] = $_FILES['csv_arq']['size'];
    $file['md5']  = md5_file($tmp_name);
    
    if($file_exist = busca_arq($file)){
        $json = array('success' => false, 'error' => 'duplicado', 'error_msg' => 'Arquivo já consta no sistema: '.$file_exist['slug'], 'file' => $file_exist);
        echo json_encode($json);
        die();
    }
    
    if($upd = upload_arq($tmp_name, $file)){
        $json = array('success' => true, 'id' => $upd, 'file' => $file);
        echo json_encode($json);
        die();
    }else{
        $json = array('success' => false, 'error' => 'err_upload', 'error_msg' => 'Erro ao fazer o upload,\r\n favor tentar novamente!');
        echo json_encode($json);
        die();
    }
}

function busca_arq($file){
    global $wpdb;

    $table_name = $wpdb->prefix . 'csv_arqs';

    $result = $wpdb->get_results ("
        SELECT * 
        FROM  $table_name 
        WHERE name = '{$file['name']}'
        AND type = '{$file['type']}'
        AND size = {$file['size']}
        AND md5 = '{$file['md5']}'
    ", ARRAY_A);
        
    if(count($result) > 0){
        return $result[0];
    }
    return false;
}

function lista_arq(){
    global $wpdb;

    $table_name = $wpdb->prefix . 'csv_arqs';

    $result = $wpdb->get_results ("
        SELECT * 
        FROM  $table_name
    ", ARRAY_A);
        
    if(count($result) > 0){
        $ret = "";
        foreach($result as $arq){
            $ret .= "<option value='{$arq['id']}'>{$arq['slug']}</option>\r\n";
        }
        
        return $ret;
    }
    return "";
}

function upload_arq($tmp_name, &$file){
    $uploaddir = str_replace("admin/", "", plugin_dir_path( __FILE__ ));
    $uploaddir .= "csv\\";
    
    $file['slug'] = date('d-m-Y-H-i-') . basename($file['name']);
    $uploadfile = $uploaddir . $file['slug'];

    if (move_uploaded_file($tmp_name, $uploadfile)) {
        if($id = insert_table_csv_arq($file)){
            if(insert_dados_csv($id)){
                return $id;
            }else{
                delete_table_csv_arq($id);
                unlink($uploadfile);
                unlink($tmp_name);
                return false;
            }
        } else {
            unlink($uploadfile);
            unlink($tmp_name);
            return false;
        }
    } else {
        unlink($tmp_name);
        return false;
    }
}

function insert_table_csv_arq($file){
    global $wpdb;

    $table_name = $wpdb->prefix . 'csv_arqs';

    if($wpdb->insert( $table_name, $file )){
        return $wpdb->insert_id;
    }
    return false;
}

function delete_table_csv_arq($id){
    global $wpdb;

    $table_name = $wpdb->prefix . 'csv_arqs';

    return $wpdb->delete( $table_name, array( 'id' => $id ) );
}

// Gravar dados do csv
function insert_dados_csv($id){
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'csv_arqs';

    $result = $wpdb->get_results ("
        SELECT *
        FROM  $table_name
        WHERE id = '$id'
    ", ARRAY_A);
        
    if(count($result) > 0){
        $uploaddir = str_replace("admin/", "", plugin_dir_path( __FILE__ ));
        $uploaddir .= "csv\\";
        $filename = $uploaddir . $result[0]['slug'];
        
        $delimitador = ';';
        $cerca = '"';
        $f = fopen($filename, 'r');
        if ($f) { 

            $cabecalho = fgetcsv($f, 0, $delimitador, $cerca);

            while (!feof($f)) { 
                
                $linha = fgetcsv($f, 0, $delimitador, $cerca);
                if (!$linha) {
                    continue;
                }

                $col = 0;
                foreach($linha as $k => $cell){
                    if($k > 1 && $cell != ''){
                        $ano = substr($cabecalho[$k], 2);
                        $dados = array('id_csv_arq' => $id, 'id_loc' => $linha[0], 'id_var' => $linha[1], 'ano' => $ano, 'valor' => $cell);
                        if(!insert_dados_linha($dados)){
                            delete_dados_from_csv($id);
                            return false;
                        }
                    }
                }

            }
            fclose($f);
        }
        return true;
    }
    return false;
}

function insert_dados_linha($dados){
    global $wpdb;

    $table_name = $wpdb->prefix . 'dados_graph';

    if($wpdb->insert( $table_name, $dados )){
        return $wpdb->insert_id;
    }
    return false;
}

function delete_dados_from_csv($id){
    global $wpdb;

    $table_name = $wpdb->prefix . 'dados_graph';

    return $wpdb->delete( $table_name, array( 'id_csv_arq' => $id ) );
}

// Mostra dados do csv
add_action( 'admin_post_carrega_dados_csv', 'carrega_dados_csv' );
function carrega_dados_csv(){
    global $wpdb;
    
    $id = $_POST['sel_file'];

    $table_name = $wpdb->prefix . 'csv_arqs';

    $result = $wpdb->get_results ("
        SELECT *
        FROM  $table_name
        WHERE id = '$id'
    ", ARRAY_A);
        
    if(count($result) > 0){
        $ret = "";
        $uploaddir = str_replace("admin/", "", plugin_dir_path( __FILE__ ));
        $uploaddir .= "csv\\";
        $filename = $uploaddir . $result[0]['slug'];
        
        $delimitador = ';';
        $cerca = '"';
        $f = fopen($filename, 'r');
        if ($f) { 

            //$cabecalho = fgetcsv($f, 0, $delimitador, $cerca);
            $ret .= "<table id='csv_data'>";

            while (!feof($f)) { 

                $linha = fgetcsv($f, 0, $delimitador, $cerca);
                if (!$linha) {
                    continue;
                }
                $ret .= "<tr>";

                //$registro[] = $linha;//array_combine($cabecalho, $linha);
                foreach($linha as $cell){
                    $ret .= "<td>$cell</td>";
                }
                $ret .= "</tr>";

            }
            $ret .= "</table>";
            fclose($f);
        }
        
        echo $ret;
        
        //return $ret;
    }
    return "";
}

function lista_conf(){
    global $wpdb;

    $table_name = $wpdb->prefix . 'tb_grafico';

    $result = $wpdb->get_results ("
        SELECT id, nome 
        FROM  $table_name
        ORDER BY id
    ", ARRAY_A);
        
    if(count($result) > 0){
        $ret = "";
        foreach($result as $conf){
            $ret .= "<option value='{$conf['id']}'>{$conf['nome']}</option>\r\n";
        }
        
        return $ret;
    }
    return "";
}

add_action( 'admin_post_carrega_conf_graph', 'carrega_conf_graph' );
function carrega_conf_graph(){
    global $wpdb;

    $id_graph = $_POST['sel_conf'];

    $table_name = $wpdb->prefix . 'tb_grafico';
    
    $result = $wpdb->get_results ("
        SELECT *
        FROM  $table_name
        WHERE id = $id_graph
        ORDER BY id
    ", ARRAY_A);
    
    $ret = $result[0];
    
    $table_name = $wpdb->prefix . 'tb_variavel_grafico';
    $table_name2 = $wpdb->prefix . 'tb_variavel';
    
    $result = $wpdb->get_results ("
        SELECT vg.*, v.nome
        FROM $table_name vg
        JOIN $table_name2 v ON v.id = vg.id_var
        WHERE vg.id_grafico = $id_graph
        ORDER BY vg.ordem
    ", ARRAY_A);
    
    $ret['variaveis'] = $result;
    
    echo json_encode($ret);
        
    return "";
}

add_action( 'admin_post_lista_var', 'lista_var' );
function lista_var(){
    global $wpdb;

    $table_name = $wpdb->prefix . 'tb_variavel';

    $result = $wpdb->get_results ("
        SELECT id, nome 
        FROM  $table_name
        ORDER BY id
    ", ARRAY_A);

    if(strtoupper($_POST['response']) == 'JSON'){
        echo json_encode($result);
        return "";
    }
        
    if(count($result) > 0){
        $ret = "";
        foreach($result as $var){
            $ret .= "<option value='{$var['id']}'>{$var['id']} - {$unid['nome']}</option>\r\n";
        }
        
        return $ret;
    }
    return "";
}

add_action( 'admin_post_lista_unid', 'lista_unid' );
function lista_unid(){
    global $wpdb;

    $table_name = $wpdb->prefix . 'tb_unidade';

    $result = $wpdb->get_results ("
        SELECT id, nome 
        FROM  $table_name
        ORDER BY id
    ", ARRAY_A);
        
    if(strtoupper($_POST['response']) == 'JSON'){
        echo json_encode($result);
        return "";
    }
        
    if(count($result) > 0){
        $ret = "";
        foreach($result as $unid){
            $ret .= "<option value='{$unid['id']}'>{$unid['nome']}</option>\r\n";
        }
        
        return $ret;
    }
    return "";
}

add_action( 'admin_post_lista_label', 'lista_label' );
function lista_label(){
    global $wpdb;

    $table_name = $wpdb->prefix . 'tb_label';

    $result = $wpdb->get_results ("
        SELECT id, nome 
        FROM  $table_name
        ORDER BY id
    ", ARRAY_A);
        
    if(strtoupper($_POST['response']) == 'JSON'){
        echo json_encode($result);
        return "";
    }

    if(count($result) > 0){
        $ret = "";
        foreach($result as $label){
            $ret .= "<option value='{$label['id']}'>{$label['nome']}</option>\r\n";
        }
        
        return $ret;
    }
    return "";
}

add_action( 'admin_post_save_graph_conf', 'save_graph_conf' );
function save_graph_conf(){
    global $wpdb;
    
    //print_r($_POST);

    $dados = array();
    $dados['nome'] = $_POST['nome'];
    $dados['complemento_nome'] = $_POST['complemento_nome'];
    $dados['tipo'] = $_POST['tipo'];
    $dados['todos_anos'] = $_POST['todos_anos'];
    $dados['id_unid'] = $_POST['id_unid'];

    $table_name = $wpdb->prefix . 'tb_grafico';

    if($wpdb->insert( $table_name, $dados )){
        $id = $wpdb->insert_id;
    }else{
        echo json_encode(array('sucess' => false));
        return false;
    }

    $vars = array('id_grafico' => $id, 'ordem' => 1);
    foreach($_POST['var'] as $k => $id_var){
        $vars['id_var'] = $id_var;
        $vars['id_unid'] = $_POST['unid'][$k];
        $vars['id_label'] = $_POST['label'][$k];

        if($id_var != '' && $vars['id_unid'] != '' && $vars['id_label'] != ''){
            
            $table_name = $wpdb->prefix . 'tb_variavel_grafico';

            if($wpdb->insert( $table_name, $vars )){
                $id_gv = $wpdb->insert_id;
            }else{
                $wpdb->delete( $table_name, array( 'id_grafico' => $id ) );
                $table_name = $wpdb->prefix . 'tb_grafico';
                $wpdb->delete( $table_name, array( 'id' => $id ) );
                $wpdb->get_results("ALTER TABLE $table_name AUTO_INCREMENT = 1;");
                echo json_encode(array('sucess' => false));
                return false;
            }
            $vars['ordem']++;
        }
    }

    echo json_encode(array('success' => true, 'id' => $id, 'nome' => $dados['nome']));
    return true;
}

add_action( 'admin_post_dados_var', 'dados_var' );
function dados_var(){
    global $wpdb;
    
    $id = $_POST['id'];
    
    $table_name = $wpdb->prefix . 'tb_variavel';
    
    $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name 
             WHERE id = %d", $id));
    
    echo json_encode($row);
}

add_action( 'admin_post_monta_graph', 'monta_graph' );
function monta_graph($post_vars = false){
    global $wpdb;
    
    $ret = array('success' => true);
    $anos = array();
    
    $id_graph = (isset($_POST['id_graph'])?$_POST['id_graph']:$post_vars['id']);
    $id_arq = (isset($_POST['id_arq'])?$_POST['id_arq']:$post_vars['arq']);
    
    $ret['id_arq'] = $id_arq;
    
    $query = "
        SELECT g.*, u.nome as unidade FROM {$wpdb->prefix}tb_grafico g
            JOIN {$wpdb->prefix}tb_unidade u
                ON u.id = g.id_unid
            WHERE g.id = %d
    ";
    $ret['graph'] = $wpdb->get_row( $wpdb->prepare( $query , $id_graph));

    $query = "
        SELECT v.id, v.nome, u.nome as unidade, l.nome as label FROM {$wpdb->prefix}tb_variavel_grafico vg
            JOIN {$wpdb->prefix}tb_variavel v
                ON v.id = vg.id_var
            JOIN {$wpdb->prefix}tb_unidade u
                ON u.id = vg.id_unid
            JOIN {$wpdb->prefix}tb_label l
                ON l.id = vg.id_label
            WHERE vg.id_grafico = %d
            ORDER BY vg.ordem
    ";
    $ret['vars'] = $wpdb->get_results( $wpdb->prepare( $query , $id_graph));
    
    foreach($ret['vars'] as $var){
        $query = " 
            SELECT dg.*, l.nome as localidade FROM {$wpdb->prefix}dados_graph dg
                JOIN {$wpdb->prefix}tb_localidade l
                    ON l.id = dg.id_loc
                 WHERE dg.id_var = %d
                 AND dg.id_loc = 1000
        ";
        if($id_arq != 'bd'){
            $query .= " AND dg.id_csv_arq = %d ";
        }
        $query .= "
            ORDER BY dg.id_loc, dg.ano
        ";
        if($id_arq != 'bd'){
            $dados = $wpdb->get_results( $wpdb->prepare( $query , $var->id, $id_arq));
        }else{
            $dados = $wpdb->get_results( $wpdb->prepare( $query , $var->id ));
        }

        foreach($dados as $dado){
            $ret[$dado->id_loc][$dado->id_var][$dado->ano] = $dado;
            $ret['localidades'][$dado->id_loc] = $dado->localidade;
            $anos[(int)$dado->ano] = true;
        }
        ksort($anos);

        $ret[$dado->id_loc]['cols'] = array(
                array('type' => 'string', 'name' => 'Variável')//,
                //array('type' => 'string', 'name' => 'Localidade')
            );

        foreach($anos as $ano => $t){
            //$ret['anos'][] = $ano;
            $ret[$dado->id_loc]['cols'][] = array('type' => 'number', 'name' => $ano);
        }

    }

    if($post_vars) return $ret;
    echo json_encode($ret);
}

add_action( 'admin_post_add_var', 'add_var' );
function add_var(){
    global $wpdb;
    $dados = array();

    $ret = array('func'=> 'add_var', 'success' => false);
    
    $dados['id'] = trim($_POST['id']);
    $dados['nome'] = trim($_POST['nome']);
    $dados['id_unid'] = trim($_POST['id_unid']);
    $dados['id_label'] = trim($_POST['id_label']);
    
    $table_name = $wpdb->prefix . 'tb_variavel';

    if($row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE id = %d", $dados['id']))){
        $ret = array('func'=> 'add_var', 'success' => false, 'id' => $row->id, 'nome' => $row->nome);
    }else{
        if($wpdb->insert( $table_name, $dados )){
            $ret = array('func'=> 'add_var', 'success' => true, 'id' => $dados['id'], 'nome' => $dados['nome']);
        }
    }
    echo json_encode($ret);
}

add_action( 'admin_post_add_label', 'add_label' );
function add_label(){
    global $wpdb;
    $dados = array();
    
    $ret = array('func'=> 'add_label', 'success' => false);
    
    $dados['nome'] = trim($_POST['nome']);
    
    $table_name = $wpdb->prefix . 'tb_label';

    if($row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE nome = %s", $dados['nome']))){
        $ret = array('func'=> 'add_label', 'success' => false, 'id' => $row->id);
    }else{
        if($wpdb->insert( $table_name, $dados )){
            $id = $wpdb->insert_id;
            $ret = array('func'=> 'add_label', 'success' => true, 'id' => $id, 'nome' => $dados['nome']);
        }
    }
    echo json_encode($ret);
}

add_action( 'admin_post_add_unid', 'add_unid' );
function add_unid(){
    global $wpdb;
    $dados = array();
    
    $ret = array('func'=> 'add_unid', 'success' => false);
    
    $dados['nome'] = trim($_POST['nome']);
    
    $table_name = $wpdb->prefix . 'tb_unidade';

    if($row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE nome = %s", $dados['nome']))){
        $ret = array('func'=> 'add_unid', 'success' => false, 'id' => $row->id);
    }else{
        $row = $wpdb->get_row( "SELECT MAX(id) as last FROM $table_name" );
        $dados['id'] = $row->last + 1;
        if($wpdb->insert( $table_name, $dados )){
            $ret = array('func'=> 'add_unid', 'success' => true, 'id' => $dados['id'], 'nome' => $dados['nome']);
        }else{
            print_r($wpdb->last_error);
        }
    }
    echo json_encode($ret);
}
