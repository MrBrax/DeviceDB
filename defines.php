<?php

$device_flags_sql = [
	1 	=> [ "name" => "Active Directory", 	"short" => "a",		"col" => "acd" 			], 
	2 	=> [ "name" => "Storage", 			"short" => "s",		"col" => "storage" 		],
	3 	=> [ "name" => "Outside",			"short" => "o",		"col" => "outside" 		],
	4 	=> [ "name" => "Needs repair",		"short" => "n",		"col" => "needs_repair"	],
	5 	=> [ "name" => "Repairing",			"short" => "r",		"col" => "repairing" 	],
	6 	=> [ "name" => "BYOD",				"short" => "b",		"col" => "byod" 		],
	7 	=> [ "name" => "Public",			"short" => "p",		"col" => "public" 		],
	8 	=> [ "name" => "Travel",			"short" => "t",		"col" => "travel" 		],
	9 	=> [ "name" => "Dyslexia",			"short" => "d",		"col" => "dyslexia" 	],
	10 	=> [ "name" => "Resigned",			"short" => "rs",	"col" => "resigned" 	],
	11	=> [ "name" => "Missing",			"short" => "m",		"col" => "missing" 		]
];

$device_info_sql = [
	1	=> [ "name" => "BIOS Password", 	"enum" => "BIOS_PWD",		"type" => "string" ],
	2	=> [ "name" => "Account name", 		"enum" => "ACCOUNT_NAME",	"type" => "string" ],
	3	=> [ "name" => "Account password", 	"enum" => "ACCOUNT_PWD",	"type" => "string" ]
];

$model_info_sql = [
	1	=> [ "name" => "Enter BIOS", 		"enum" => "BIOS_ENTER",			"type" => "string" ],
	2	=> [ "name" => "Boot device menu", 	"enum" => "BOOT_DEVICE_MENU",	"type" => "string" ],
	3	=> [ "name" => "Network boot", 		"enum" => "BOOT_NETWORK",		"type" => "string" ],
];