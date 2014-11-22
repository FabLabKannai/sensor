/**
 * config.h
 * FabLab Kannai Sensor Project
 * 2014-08-20
 */ 

#ifndef CONFIG_H
#define CONFIG_H

// === Access Point ===
// please rewrite your SSID & password
#define AP_SSID "your_ssid"
#define AP_PASS "your_password"

// === Server ===
// this is test server
// please rewrite your host and path
// this url is shared with those of other
#define SERVER_HOST  "kvps-180-235-254-171.secure.ne.jp"
#define SERVER_PATH  "/~fablab/sensor/post.php"

// === Upload Interval ===
#define TIME_UPLOAD  1000  // 1 sec
//#define TIME_UPLOAD  60000  // 60 sec

#endif
