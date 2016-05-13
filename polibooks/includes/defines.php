<?php

//if(!apc_load_constants('constants'))
//{
	$constants = array(
		"THEME_BASE"		=> 0,
		"THEME_SINGLE"		=> 1,
		"THEME_MULTIPLE"	=> 2,
		
		/* user roles */
		"USER_ROLE_NORMAL"	=> 0,
		"USER_ROLE_PARTNER"	=> 1,
		"USER_ROLE_MOD"		=> 2,
		"USER_ROLE_ADMIN"	=> 3,

		/* user roles settings */
		"USER_ROLE_MASK"	=> 3,

		/* user status bits */
		"USER_ENABLED"		=> 4,
		"USER_BANNED"		=> 8,
		"USER_PUBLISH"		=> 16,
		"USER_SHOWPHONE"	=> 32,
		"USER_SHOWMAIL"		=> 64,
		"USER_SHOWFB"		=> 128,

		/* books per user settings */
		/* SSS,BBB,P */
		"BPU_STATUS_NOT_PUBLISHED"	=> 0,
		"BPU_STATUS_PUBLISHED"		=> 1,
		"BPU_STATUS_EXPIRED"		=> 2,
		"BPU_STATUS_BANNED"			=> 3,
		"BPU_STATUS_SOLD"			=> 4,
		"BPU_STATUS_REMOVED"		=> 5,
		
		"USER_GUEST"	=> 1,
		"USER_AUTHED"	=> 2,
		"USER_ADMIN"	=> 4
	);

    foreach($constants as $k=>$v) {
        define($k,$v);
    }
//	apc_define_constants('constants', $constants);
//}

$books_quality = array(
		1 => "Mai aperto",
		2 => "Usato senza sottolineare",
		3 => "Sottolineato a matita",
		4 => "Sottolineato a penna",
		5 => "Danneggiato ma leggibile"
	);
?>
