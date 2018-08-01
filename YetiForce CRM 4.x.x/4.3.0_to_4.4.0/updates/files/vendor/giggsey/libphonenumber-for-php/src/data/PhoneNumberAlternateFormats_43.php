<?php
/**
 * This file has been @generated by a phing task by {@link BuildMetadataPHPFromXml}.
 * See [README.md](README.md#generating-data) for more information.
 *
 * Pull requests changing data in these files will not be accepted. See the
 * [FAQ in the README](README.md#problems-with-invalid-numbers] on how to make
 * metadata changes.
 *
 * Do not modify this file directly!
 */

return [
  'id' => '',
  'countryCode' => 43,
  'internationalPrefix' => '',
  'sameMobileAndFixedLinePattern' => false,
  'numberFormat' => [
	0 => [
	  'pattern' => '(1)(\\d{3})(\\d{2})(\\d{2,3})',
	  'format' => '$1 $2 $3 $4',
	  'leadingDigitsPatterns' => [
		0 => '1',
	  ],
	  'nationalPrefixFormattingRule' => '',
	  'domesticCarrierCodeFormattingRule' => '',
	  'nationalPrefixOptionalWhenFormatting' => false,
	],
	1 => [
	  'pattern' => '(5)(\\d{3,12})',
	  'format' => '$1 $2',
	  'leadingDigitsPatterns' => [
		0 => '5[079]',
	  ],
	  'nationalPrefixFormattingRule' => '',
	  'domesticCarrierCodeFormattingRule' => '',
	  'nationalPrefixOptionalWhenFormatting' => false,
	],
	2 => [
	  'pattern' => '(50)(\\d{2})(\\d{2})(\\d{2,4})',
	  'format' => '$1 $2 $3 $4',
	  'leadingDigitsPatterns' => [
		0 => '50',
	  ],
	  'nationalPrefixFormattingRule' => '',
	  'domesticCarrierCodeFormattingRule' => '',
	  'nationalPrefixOptionalWhenFormatting' => false,
	],
	3 => [
	  'pattern' => '(5\\d)(\\d{2})(\\d{2})(\\d{2})(\\d{2,4})',
	  'format' => '$1 $2 $3 $4 $5',
	  'leadingDigitsPatterns' => [
		0 => '5[079]',
	  ],
	  'nationalPrefixFormattingRule' => '',
	  'domesticCarrierCodeFormattingRule' => '',
	  'nationalPrefixOptionalWhenFormatting' => false,
	],
	4 => [
	  'pattern' => '(5\\d)(\\d{5})(\\d{4,6})',
	  'format' => '$1 $2 $3',
	  'leadingDigitsPatterns' => [
		0 => '5[079]',
	  ],
	  'nationalPrefixFormattingRule' => '',
	  'domesticCarrierCodeFormattingRule' => '',
	  'nationalPrefixOptionalWhenFormatting' => false,
	],
	5 => [
	  'pattern' => '(5\\d)(\\d{6,7})',
	  'format' => '$1 $2',
	  'leadingDigitsPatterns' => [
		0 => '5[079]',
	  ],
	  'nationalPrefixFormattingRule' => '',
	  'domesticCarrierCodeFormattingRule' => '',
	  'nationalPrefixOptionalWhenFormatting' => false,
	],
	6 => [
	  'pattern' => '(\\d{3})(\\d{3})(\\d{3})(\\d{3,4})',
	  'format' => '$1 $2 $3 $4',
	  'leadingDigitsPatterns' => [
		0 => '316|46|51|732|6(?:44|5[0-3579]|[6-9])|7(?:1|[28]0)|[89]',
	  ],
	  'nationalPrefixFormattingRule' => '',
	  'domesticCarrierCodeFormattingRule' => '',
	  'nationalPrefixOptionalWhenFormatting' => false,
	],
	7 => [
	  'pattern' => '(\\d{3})(\\d{3})(\\d{2})(\\d{2,3})',
	  'format' => '$1 $2 $3 $4',
	  'leadingDigitsPatterns' => [
		0 => '316|46|51|732|6(?:44|5[0-3579]|[6-9])|7(?:1|[28]0)|[89]',
	  ],
	  'nationalPrefixFormattingRule' => '',
	  'domesticCarrierCodeFormattingRule' => '',
	  'nationalPrefixOptionalWhenFormatting' => false,
	],
	8 => [
	  'pattern' => '(\\d{4})(\\d{3})(\\d{3,4})',
	  'format' => '$1 $2 $3',
	  'leadingDigitsPatterns' => [
		0 => '2|3(?:1[1-578]|[3-8])|4[2378]|5[2-6]|6(?:[12]|4[1-35-9]|5[468])|7(?:2[1-8]|35|4[1-8]|[5-79])',
	  ],
	  'nationalPrefixFormattingRule' => '',
	  'domesticCarrierCodeFormattingRule' => '',
	  'nationalPrefixOptionalWhenFormatting' => false,
	],
	9 => [
	  'pattern' => '(\\d{3})(\\d{2})(\\d{2})(\\d{2,3})',
	  'format' => '$1 $2 $3 $4',
	  'leadingDigitsPatterns' => [
		0 => '316|46|51|732|6(?:44|5[0-3579]|[6-9])|7(?:1|[28]0)|[89]',
	  ],
	  'nationalPrefixFormattingRule' => '',
	  'domesticCarrierCodeFormattingRule' => '',
	  'nationalPrefixOptionalWhenFormatting' => false,
	],
  ],
  'intlNumberFormat' => [
  ],
  'mainCountryForCode' => false,
  'leadingZeroPossible' => false,
  'mobileNumberPortableRegion' => false,
];