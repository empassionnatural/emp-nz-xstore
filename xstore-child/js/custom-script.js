jQuery(document).ready(function($){
    console.log('test');
    $date = new Date();
    $day = $date.getUTCDate();
    $year = $date.getUTCFullYear();
    $month = $date.getUTCMonth();

    console.log( $month );
    console.log( $day );
    console.log( $year );

    var nz_time = moment().add(2,'h').format( 'MM-DD-YYYY hh:mm a ZZ' );
    console.log(nz_time);
    var aug_1st = moment( 'August 1 2018' ).format('MM-DD-YYYY');
    console.log(aug_1st);

    var aug_1_unix = moment( 'July 24 2018' ).format('x');

    var set_nz_tz = moment().utcOffset( "+12:00" ).format('x');

    if( set_nz_tz >= aug_1_unix ) {
        $('#shipping-fee').text('$8.50');
    }



});