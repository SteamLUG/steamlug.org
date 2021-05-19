<?php
$pageTitle = 'Member Stats';
date_default_timezone_set('UTC');
include_once( 'includes/header.php' );
include_once( 'includes/functions_stats.php' );
// include_once( 'includes/functions_charts.php' );

$members = getMemberStats();
if ( $members ) {
	$most = 0;
	$datestart = null;
	$dateend = 0;

	$data = array( );
	$years = array();
	foreach ( getMemberStats( ) as $entry ) {

		if ( $entry['count'] == 0 ) {
			// skip borky data
			continue;
		}

		$date = strtotime( $entry['date'] );
		$years[] = date( 'Y', $date );
		if ( ! isset( $datestart ) )
			$datestart = $date;
		$datestart	= min( $datestart, $date );
		$dateend	= max( $dateend, $date );
		$most = max( $entry['countpublic'], $most );
		$most = max( $entry['count'], $most );
		$data[ $date ] = $entry['count'];
	}

	// TODO pull this into a graphing function, move the date stuff above into the func,
	// and make this foreach above just prepare $data to pass in; year/date stuff can be inferred

	$years = array_unique( $years );
	sort( $years );
	// Use this to provide padding to the start of the year before data.
	//$firstyear = mktime( 0, 0, 0, 1, 1, $years[0] );
	//$datestart = min( $firstyear, $date );
	$datewidth = $dateend - $datestart;

	// never have data touching the ceiling
	$most = ( ceil( ( $most * 1.1 ) / 1000 ) * 1000 );

	// TODO make this a better scalable SVG.
	// TODO because I bet it looks like butt on mobile!
	$chartheight = 478; $chartwidth = 935;
	$chartpadding = 5;  $chartheader = 35;
	$chartextents = $chartheight + $chartheader; //[]
	$svgsize = [ $chartwidth+$chartpadding, $chartheight + $chartheader + $chartheader - 3 ];
	$cropsize = [ $chartwidth - 6, $chartheight ];

	$points = 'M 0 ' . $chartheight . ' ';
	foreach ( $data as $date => $count ) {

		$value = $chartheight - round( ( ( $count / $most ) * $chartheight ), 3);
		$progress = round(( ( $date - $datestart ) / $datewidth ) * $chartwidth, 3);
		$points .= ' L ' . $progress . ' ' . $value;
	}
	$line = $points . ' L ' . $chartwidth . ' ' . $chartheight;

	// Y Axis labels and grid
	$yticks = $most / 1000;
	$yaxisgrid = '';
	$yaxislabels = '';
	foreach ( range( 0, $yticks ) as $tick ) {
		$val = 34.5 + ( $chartheight - round( ( ($tick * 1000) / $most ) * $chartheight, 0 ) );
		$yaxislabels .= '<text y="' . ($val - 1.5) . '" x="-3">' . ( $tick != 0 ? $tick . 'k' : $tick ) . '</text>';
		$yaxisgrid .= ' M 5 ' . $val . ' L ' . $chartwidth . ' ' . $val;
	}

	$xticks = count( $years ) - 1;
	// [ $datestart .... 0  ←→  xticks ... $dateend ]
	// |<--                 datewidth            -->|
	$xaxisgrid = '';
	$xaxisticks = '';
	$xaxislabels = '';
	foreach ( range( 0, $xticks ) as $tick ) {
		$year = $years[$tick];
		$peg = mktime( 0, 0, 0, 1, 1, $year );
		if ( ( $peg <= $datestart ) or ( $peg > $dateend ) )
			continue;
		$peg = $peg - $datestart;
		$peg = $peg / $datewidth;
		$peg = $peg * $chartwidth;
		$peg = round( $peg, 0 ) + 0.5;
		$xaxisgrid .= 'M ' . $peg . ' ' . $chartheader . ' L ' . $peg . ' ' . ( $chartheight + $chartheader) . ' ';
		$xaxisticks .= 'M ' . $peg . ' ' . ( $chartheight + $chartheader ) . ' L ' . $peg . ' ' . ( $chartheight + $chartheader + 10 ) . ' ';
		$xaxislabels .= '<text x="' . $peg . '" y="54">' . $year . '</text>';
	}

	echo <<<SVGHERE
	<div class="col-md-12">
		<article class="panel panel-default">
			<header class="panel-heading">
				<h3 class="panel-title">Group Member Count</h3>
			</header>
			<div class="panel-body">

	<div style="position: relative; overflow: hidden; width:{$svgsize[0]}px;height:{$svgsize[1]}px; margin: 0 auto">
	<svg version="1.1" xmlns="http://www.w3.org/2000/svg" width="{$svgsize[0]}" height="{$svgsize[1]}">
	<defs><clipPath id="crop-edges"><rect x="0.5" y="0" width="{$cropsize[0]}" height="{$cropsize[1]}" fill="none"></rect></clipPath></defs>
	<rect fill="white" test="#999" class="background" x="0" y="0" width="{$svgsize[0]}" height="{$svgsize[1]}" rx="0" ry="0"></rect>
	<g style="fill:none;stroke-width:1;opacity:1;stroke:#ccd6eb;">
		<path class="grid xaxis" style="stroke:#e6e6e6" d="{$xaxisgrid}"></path>
		<path class="grid yaxis" style="stroke:#e6e6e6" d="{$yaxisgrid}"></path>
		<path class="line yaxis" d="M {$chartpadding} {$chartheader} L {$chartpadding} {$chartextents} M {$chartwidth} {$chartheader} L {$chartwidth} {$chartextents}"></path>
		<path class="line xaxis" d="M {$chartpadding} {$chartextents}.5 L {$chartwidth} {$chartextents}.5"></path>
		<path class="tick xaxis" d="{$xaxisticks}"></path>
	</g>
	<g transform="translate({$chartpadding},{$chartheader})" clip-path="url(#crop-edges)"><path class="chart" stroke="#4A89DC" fill="rgba(74,137,220,0.85)" stroke-width="2" stroke-linejoin="round" stroke-linecap="round" d="{$line}"></path></g>
	<g class="labels" style="cursor:default;font-family: sans-serif;font-size:11px;fill:#333333;opacity:1;text-shadow: 1px 1px 3px #666;">
		<g class="xaxis" style="text-anchor:middle" transform="translate(0,{$chartheight})">{$xaxislabels}</g>
		<g class="yaxis" style="text-anchor:end" transform="translate({$chartwidth},0)">{$yaxislabels}</g>
	</g>
	</svg>
	</div>
			</div>
		</article>
	</div>
SVGHERE;
} else {

echo <<<NOTHINGHERE
	<div class="col-md-12">
		<article class="panel panel-default">
			<header class="panel-heading">
				<h3 class="panel-title">Group Member Count</h3>
			</header>
			<div class="panel-body">
				<p>… is in another castle.</p>
			</div>
		</article>
	</div>
NOTHINGHERE;

}
include_once( 'includes/footer.php' );
