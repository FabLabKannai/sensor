/**
 * Sensor for Server with Wifi
 * Temperature, Humidity, Light, Noise,  Air Pressure
 * 
 * FabLab Kannai Sensor Project
 * 2014-08-20
 */ 

/*
 * require
 * SFE_CC3000_Library 
 *   https://github.com/sparkfun/SFE_CC3000_Library
 *   and modify SFE_CC3000.cpp
 * Arduino Library for the DHT11
 *   https://github.com/adafruit/DHT-sensor-library
 * Driver for the Adafruit MPL115A2 barometric pressure sensor breakout
 *   https://github.com/adafruit/Adafruit_MPL115A2
 */

/**
 * send data
 * { "temperature":11.1, "humidity":22.2, "light":33.3, "noise":44.4, "pressure":55.5 }
 *
 * response
 * { "code": 1 }
 */

/**
 * Sensor Device
 * Temperature & Humidity : DHT11
 * Air Pressure : MPL115A2
 * Light : NJL7502L
 * Noise : Microphone C9767BB422LFP
 */

// WiFi 
#include <SPI.h>
#include <SFE_CC3000.h>
#include <SFE_CC3000_Client.h>

// Air Pressure
#include <Wire.h>
#include <Adafruit_MPL115A2.h>

// Temperature & Humidity
#include <DHT.h>

#include <avr/wdt.h>
#include "config.h"

// Pins
#define CC3000_INT  2  // Needs to be an interrupt pin (D2/D3)
#define CC3000_EN  7  // Can be any digital pin
#define CC3000_CS  10  // Preferred is pin 10 on Uno
#define PIN_DHT  3
#define PIN_LED  9
#define PIN_LIGHT  A0
#define PIN_NOISE  A1

// param
#define PARAM_TEMP   "param={\"temperature\":"
#define PARAM_HUMI   ",\"humidity\":"
#define PARAM_LIGHT  ",\"light\":"
#define PARAM_NOISE  ",\"noise\":"
#define PARAM_PRESS  ",\"pressure\":"
#define PARAM_END    "}"

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

// Access Point
#define IP_ADDR_LEN  4  // Length of IP address in bytes
#define AP_TIMEOUT  30000  // 30 sec
#define AP_SECURITY  WLAN_SEC_WPA2  // Security of network

// Server
#define SERVER_PORT  80
#define SERVER_PATH_BEGIN  "/~"

// Response
#define TIME_RESPONSE  1  // 1 msec
#define RESPONSE_TIMEOUT  5000  // 5 sec

// loop
#define MAX_NOT_SEND  10
#define TIME_LOOP  1000  // 1 sec

// CC3000
SFE_CC3000 wifi = SFE_CC3000( CC3000_INT, CC3000_EN, CC3000_CS );
SFE_CC3000_Client client = SFE_CC3000_Client( wifi );

// DHT
DHT dht( PIN_DHT, DHTTYPE );

// MPL115A2
Adafruit_MPL115A2 mpl115a2;

// Server IP Address
IPAddress host_addr;

// length of param characters
int param_len = 0;

// led
boolean led_status = false;

// counter that cannot send wifi 
unsigned int cnt_not_send = 0;

// time which excute to send data
unsigned long time_excute = 0;

boolean is_connected = false;

// proc wdt_reset if 1
boolean is_wdt = true;

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
	setup_wifi();
	setup_param_len();
	wdt_enable( WDTO_8S );
}

/**
 * setup_wifi
 */
void setup_wifi() {  
	// Initialize CC3000 (configure SPI communications)
	if ( wifi.init() ) {
		Serial.println( F("CC3000 initialization complete") );
	} else {
		Serial.println( F("Something went wrong during CC3000 init!") );
	} 
}

/**
 * setup_param_len
 */
void setup_param_len() {  
	param_len = 0;
	String str = PARAM_TEMP;
	param_len += str.length();
	str = PARAM_HUMI ; 
	param_len += str.length();
	str =  PARAM_LIGHT ;  
	param_len += str.length();
	str = PARAM_NOISE ;
	param_len += str.length();
	str = PARAM_PRESS ;
	param_len += str.length();
	str = PARAM_END ;
	param_len += str.length();
}
	
/**
 * === loop ===
 */ 
void loop() {
	// every 60 sec
	if ( (millis() - time_excute) > TIME_UPLOAD ) {
		time_excute = millis();
		exec_wifi();
	}
	// if wdt enabled
	if ( is_wdt ) {
		wdt_reset();
		// blink LED
		led_status = !led_status;
		if ( led_status ) {
			digitalWrite( PIN_LED, HIGH );
		} else {
  			digitalWrite( PIN_LED, LOW );
		}
	}
	// wait 1 sec
	delay( TIME_LOOP ); 
}

/**
 * exec_wifi
 */
void exec_wifi() {
	is_wdt = true;
	boolean is_ok = false;
	if ( is_connected && wifi.getConnectionStatus() ) {
		if ( send_recv_wifi() ) {
			is_ok = true;
			cnt_not_send = 0;
		}
	} else {
		is_connected = connect_wifi(); 
	}
	if ( !is_ok ) {
		cnt_not_send ++;
	}
	if ( cnt_not_send  > MAX_NOT_SEND ) {
		is_wdt = false;
	}
}

/**
 * connect_wifi
 */
boolean connect_wifi() {
	ConnectionInfo connection_info;
	int i;

	// Connect using DHCP
	Serial.println();
	Serial.print( F("Connecting to SSID: ") );
	Serial.println( F(AP_SSID) );
	if( !wifi.connect( AP_SSID, AP_SECURITY, AP_PASS, AP_TIMEOUT )) {
		Serial.print( F("Error: Could not connect to AP : ") );
		Serial.println( cnt_not_send );
		return false;
	}

	// Gather connection details and print IP address
	if ( !wifi.getConnectionInfo( connection_info ) ) {
		Serial.println( F("Error: Could not obtain connection details") );
		return false;
	}	
	
	Serial.print( F("AP IP Address: ") );
	for (i = 0; i < IP_ADDR_LEN; i++) {
		Serial.print( connection_info.ip_address[ i ] );
		if ( i < IP_ADDR_LEN - 1 ) {
			Serial.print( F(".") );
		}
	}
	Serial.println();

	/* Perform a DNS lookup of the site */
    if ( !wifi.dnsLookup( SERVER_HOST, &host_addr )) {
		Serial.println( F("Error: Could not DNS Lookup") );
        return false;
    }

	Serial.print( F("HOST IP Address: ") );
	for (i = 0; i < IP_ADDR_LEN; i++) {
		Serial.print( host_addr[ i ] );
		if ( i < IP_ADDR_LEN - 1 ) {
			Serial.print( F(".") );
		}
	}
	Serial.println();

	return true;
}

/**
 * send wifi 
 */
boolean send_recv_wifi() {
// --- read sensor ---
	wdt_reset();
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
	
	int len = param_len;
	len += str_temp.length();
	len += str_humi.length();
	len += str_light.length();
	len += str_noise.length();
	len += str_press.length();

// --- connection ---
	wdt_reset();
	Serial.println();
	Serial.print( F("Connect to ") );
	Serial.println( F(SERVER_HOST) );
	if ( !client.connect( host_addr, SERVER_PORT ) ) {
		Serial.print( F("Error: Could not make a TCP connection : ") );
		Serial.println( cnt_not_send );
		return false;
	}

// --- print ---
	wdt_reset();					
	//	server path
	Serial.print( F(SERVER_PATH) );
	Serial.println();
	//	body
	Serial.print( F(PARAM_TEMP) );
	Serial.print( str_temp );
	Serial.print( F(PARAM_HUMI) );
	Serial.print( str_humi );
	Serial.print( F(PARAM_LIGHT) );
	Serial.print( str_light );
	Serial.print( F(PARAM_NOISE) );
	Serial.print( str_noise );
	Serial.print( F(PARAM_PRESS) );
	Serial.print( str_press );
	Serial.print( F(PARAM_END) );
	Serial.println();

// --- send ---
	wdt_reset();
	// send POST method            
	client.print( F("POST ") );
	//	server path
	client.print( F(SERVER_PATH) );
	client.println( F(" HTTP/1.1") );
	// Host  
	client.print( F("Host: ") );
	client.println( F(SERVER_HOST) );
	// Connection 
	client.println( F("Connection: close") );
	// Content-Length 
	client.print( F("Content-Length: ") );
	client.println( len );
	// Content-Type 
	client.println( F("Content-Type: application/x-www-form-urlencoded") );
	// end of header
	client.println();
	// body
	client.print( F(PARAM_TEMP) );
	client.print( str_temp );
	client.print( F(PARAM_HUMI) );
	client.print( str_humi );
	client.print( F(PARAM_LIGHT) );
	client.print( str_light );
	client.print( F(PARAM_NOISE) );
	client.print( str_noise );
	client.print( F(PARAM_PRESS) );
	client.print( str_press );
	client.print( F(PARAM_END) );
	client.println();

// --- recieve --
	wdt_reset();
	boolean ret = recv_wifi();
	Serial.println();
	if ( ret ) {
		Serial.println( F("code OK") );
	} else {
		Serial.println( F("code NG") );
	}

// --- close ---
	wdt_reset();
	Serial.println();
	if ( !client.close() ) {
		Serial.println( F("Error: Could not close client") );
		return false;
	}
	Serial.println( F("Closed client") ); 
	return ret;
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
 * recv_wifi
 */
boolean recv_wifi() {
	boolean is_body = false;
	boolean is_ok = false;
	char data = 0;
	char c[7] = {0, 0, 0, 0, 0, 0, 0};
    unsigned long time = millis();
	while ( client.connected() ) {
		if ( client.available() ) {
			data = client.read();
			Serial.print( data );
			c[0] = c[1];
			c[1] = c[2];
			c[2] = c[3];
			c[3] = c[4];
			c[4] = c[5];			
			c[5] = c[6];	
			c[6] = data;	
			// boundary of header and body
			if (( c[0] == 0x0D )&&( c[1] == 0x0A )&&( c[2] == 0x0D )&&( c[3] == 0x0A )) {
  is_body = true;
			}
			// "code: 1"
			if ( is_body &&( c[0] == 'c' )&&( c[1] == 'o' )&&( c[2] == 'd' )&&( c[3] == 'e' )&&( c[4] == ':' )&&( c[5] == ' ' )&&( c[6] == '1' )) {
				is_ok = true;
			}
		}
		// If 5 sec pass, go out the loop of response
		delay( TIME_RESPONSE );
		if ( (millis() - time) > RESPONSE_TIMEOUT ) {
			return false;
		}
		wdt_reset();
	}
	Serial.println();
	return is_ok;
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
