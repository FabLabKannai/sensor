/**
 * Fab Sensor
 * Temperature, Humidity, Air Pressure, Light, Noise
 * 
 * FabLab Kannai Sensor Project
 * 2014-08-20
 */ 

/*
 * require
 * Arduino Library for the DHT11
 *   https://github.com/adafruit/DHT-sensor-library
 * Driver for the Adafruit MPL115A2 barometric pressure sensor breakout
 *   https://github.com/adafruit/Adafruit_MPL115A2
 */

/**
 * send data
 * temperature:11.1, humidity:22.2, pressure:33.3, light:44.4, noise:55.5
 */

/**
 * Sensor Device
 * Temperature & Humidity : DHT11
 * Air Pressure : MPL115A2
 * Light : NJL7502L
 * Noise : Microphone C9767BB422LFP
 */

// Air Pressure
#include <Wire.h>
#include <Adafruit_MPL115A2.h>

// Temperature & Humidity
#include <DHT.h>

// Pins
#define PIN_DHT  3
#define PIN_LED  9
#define PIN_LIGHT  A0
#define PIN_NOISE  A1

// Sensor   
#define DHTTYPE  DHT11   // DHT 11 
#define UNDIFINED_VALUE  -1
#define NOISE_OFFSET 512
#define AVARAGE_NUM  100
#define DECIMAL_DIGIT  1000

// --- todo ---
#define COFF_LIGHT  0.78

// Serial
#define SERIAL_SPEED  9600

// loop
#define TIME_LOOP  500  // 0.5 sec

// DHT
DHT dht( PIN_DHT, DHTTYPE );

// MPL115A2
Adafruit_MPL115A2 mpl115a2;

// led
boolean led_status = false;

/**
 * === setup ===
 */ 
void setup() {
	// Initialize Serial port
	Serial.begin( SERIAL_SPEED );
	Serial.println();
	Serial.println( F("---------------------------") );
	Serial.println( F("FabLab Kannai Sensor Project") );
	Serial.println( F("---------------------------") );
	// setting
	pinMode( PIN_LED, OUTPUT );	
	dht.begin();
	mpl115a2.begin();	
}
	
/**
 * === loop ===
 */ 
void loop() {	
	send_data();
	// blink LED
	led_status = !led_status;
	if ( led_status ) {
		digitalWrite( PIN_LED, HIGH );
	} else {
  		digitalWrite( PIN_LED, LOW );
	}
	// wait 0.5 sec
	delay( TIME_LOOP ); 
}

/**
 * send data
 */
boolean send_data() {
// --- read sensor ---
	// DHT
    float temp = dht.readTemperature();
  	float humi = dht.readHumidity();
	// Check if any reads failed
  	if ( isnan(temp) || isnan(humi) ) {
    	temp = UNDIFINED_VALUE;
		humi = UNDIFINED_VALUE;
		Serial.println( F("Failed to read from DHT sensor!") );
  	}

  	// light
    int light_raw = analogRead( PIN_LIGHT );
    float light = COFF_LIGHT * (float)light_raw;
  	// noise
    float noise_raw = read_analog_average( PIN_NOISE );
    float noise = abs( noise_raw - NOISE_OFFSET );
  	// air pressure
    float pressure = 10 * mpl115a2.getPressure();

	String str_temp = conv_string( temp );
	String str_humi = conv_string( humi );
	String str_light = conv_string( light );
	String str_noise = conv_string( noise );
	String str_press = conv_string( pressure );

// --- print ---
	Serial.print( "temperature:" );
	Serial.print( str_temp );
	Serial.print( ", humidity:" );
	Serial.print( str_humi );
	Serial.print( ", pressure:" );
	Serial.print( str_press );
	Serial.print( ", light:" );
	Serial.print( str_light );
	Serial.print( ", noise:" );
	Serial.print( str_noise );
	Serial.println();
}

/**
 * read_analog_average
 */
float read_analog_average( int pin ) {
	long total = 0;
	for( int i=0; i < AVARAGE_NUM; i++ ) {
		total += analogRead( pin );
	}
	float average = (float)total / AVARAGE_NUM;  
	return average;
}

/**
 * conv_string
 */
String conv_string( float value ) { 
	int sign = 1;
	if ( value < 0 ) {
		value = -value;
		sign = -1;
	}
 	int integer = (int)value;
 	int deci = (int)( 1000 * ( value - (float)integer ) );
	String str = String( sign * integer );
	str.concat( "." );
	str.concat( deci );
	return str;
}
