/*
Mega2560 + HanRun HR911105A 11/46 + DHT22 + GY-651 + TinyRTC + HD44780 + Photoresistor + LEDs + Tact switch push button
HR911105A (Ethernet shield W5100 MINI-SD):
* Ethernet shield attached to pins 10, 11, 12, 13 - HanRun HR911105A
DHT22 (AM2302 Digi Temperature Humidity module):
* Connect pin 1 (on the left) of the sensor to +5V
* Connect pin 2 of the sensor to whatever your DHTPIN is
* Connect pin 4 (on the right) of the sensor to GROUND
* Connect a 10K resistor from pin 2 (data) to pin 1 (power) of the sensor
GY-651 (HMC5883L BMP085MWC 4 axis flight control module electronic compass):
* Pin 1: Connect SCL - pin 21
* Pin 2: Connect SDA - pin 20
* Pin 3: GND
* Pin 4: Connect VCC of the sensor to 3.3V (NOT 5.0V!)
* Pin 5: VCC_IN (5v) Not connected
TinyRTC (I2C DS1307 AT24C32 RTC module):
* Pin 1: GND
* Pin 2: VCC 5V
* Pin 3: SDA - pin 20
* Pin 4: SCL - pin 21
* Pin 5: Not connected
HD44780 (1602A character LCD display module LCM blue backlight):
* Pin 1: GND
* Pin 2: VCC
* Pin 3: To 10k ohm potentiometer
* Pin 4: Connect to pin 7
* Pin 5: GND
* Pin 6: Connect to pin 8
* Pin 7: Not connected
* Pin 8: Not connected
* Pin 9: Not connected
* Pin 10: Not connected
* Pin 11: Connect to pin 9
* Pin 12: Connect to pin 10
* Pin 13: Connect to pin 11
* Pin 14: Connect to pin 12
* Pin 15: VCC
* Pin 16: GND
Photoresistor (GL5539 LDR Photo Light-Dependent Resistor):
* Connect to pin A0
LEDs:
* Connect green led to pin 22 (5mm 3,2v 30mA 16000mcd)
* Connect red led to pin 24 (5mm 2,2v 30mA 6000mcd)
Tact switch push button (6*6*H4.3):
* Connect to pin 26
*/
#include <SPI.h>
#include <Ethernet.h>
#include "DHT.h"
#include <floatToString.h> // Deprecated
#include <Wire.h>
#include <Adafruit_BMP085.h>
#include <elapsedMillis.h>
#include <LiquidCrystal.h>
#include <math.h>
#define DS1307_I2C_ADDRESS 0x68  // the I2C address of Tiny RTC
#define DHTPIN 2
#define DHTTYPE DHT22   // DHT 22  (AM2302)
char buffer[25]; // sorcery trick to concat the floats to string
elapsedMillis timer0;
#define interval 3600000 //1h timer
elapsedMillis timer1;
#define interval2 1000 //1 sec timer tinyRTC
elapsedMillis timer2;
#define interval3 60000 //360000 //6 mins timer
Adafruit_BMP085 bmp;
//compass:
#define HMC5883_WriteAddress 0x1E //  i.e 0x3C >> 1
#define HMC5883_ModeRegisterAddress 0x02
#define HMC5883_ContinuousModeCommand 0x00
#define HMC5883_DataOutputXMSBAddress  0x03
DHT dht(DHTPIN, DHTTYPE);
LiquidCrystal lcd(7, 8, 9, 10, 11, 12); //Pins for the LCD
byte second, minute, hour, dayOfWeek, dayOfMonth, month, year;
// Convert normal decimal numbers to binary coded decimal
byte decToBcd(byte val)
{
    return ( (val/10*16) + (val%10) );
}
// Convert binary coded decimal to normal decimal numbers
byte bcdToDec(byte val)
{
    return ( (val/16*10) + (val%16) );
}
// Enter a MAC address for your controller below.
// Newer Ethernet shields have a MAC address printed on a sticker on the shield
byte mac[] = {
    0xDE, 0xAD, 0xBE, 0xEF, 0xFE, 0xED
}
;
// if you don't want to use DNS (and reduce your sketch size)
// use the numeric IP instead of the name for the server:
//IPAddress server(74,125,232,128);  // numeric IP for the server (no DNS)
char server[] = "temari.fr";    // name address for the server (using DNS)
// Set the static IP address to use if the DHCP fails to assign
IPAddress ip(192,168,0,177);
// Initialize the Ethernet client library
// with the IP address and port of the server
// that you want to connect to (port 80 is default for HTTP):
EthernetClient client;
const int luminosityPin = A0;
const int buttonPin = 26;
int luminosityValue = 0;
int luminosityFinal = 0;
int buttonState = 0;
int cycle = 0;
int order = 0;
int greenled = 22;
int stringpresent = 0;
int redled = 24;
// compass:
int regb=0x01;
int regbdata=0x40;
int outputData[6];
//end compass
String currentLine = ""; // string to hold the text from server
String answer = ""; // string to hold the answer
String arduinoPassword = "password123"; // string to hold the good key
String host2 = "temari.fr"; // Put your host here
String stationPath = "/t1/index.php";  // Put the path here
boolean readingAnswer = false; // if you're currently reading the answer
// Function to set the current time, change the second&minute&hour to the right time
void setDateDs1307()
{
    second =0;
    minute = 0;
    hour  = 0;
    dayOfWeek = 0;
    dayOfMonth =0;
    month =0;
    year= 0;
    Wire.beginTransmission(DS1307_I2C_ADDRESS);
    Wire.write(decToBcd(0));
    Wire.write(decToBcd(second));    // 0 to bit 7 starts the clock
    Wire.write(decToBcd(minute));
    Wire.write(decToBcd(hour));      // If you want 12 hour am/pm you need to set
    // bit 6 (also need to change readDateDs1307)
    Wire.write(decToBcd(dayOfWeek));
    Wire.write(decToBcd(dayOfMonth));
    Wire.write(decToBcd(month));
    Wire.write(decToBcd(year));
    Wire.endTransmission();
}
// Function to gets the date and time from the ds1307 and prints result
void getDateDs1307()
{
    // Reset the register pointer
    Wire.beginTransmission(DS1307_I2C_ADDRESS);
    Wire.write(decToBcd(0));
    Wire.endTransmission();
    Wire.requestFrom(DS1307_I2C_ADDRESS, 7);
    second     = bcdToDec(Wire.read() & 0x7f);
    minute     = bcdToDec(Wire.read());
    hour       = bcdToDec(Wire.read() & 0x3f);  // Need to change this if 12 hour am/pm
    dayOfWeek  = bcdToDec(Wire.read());
    dayOfMonth = bcdToDec(Wire.read());
    month      = bcdToDec(Wire.read());
    year       = bcdToDec(Wire.read());
    lcd.clear();
    lcd.setCursor(0, 0);
    lcd.print(hour, DEC);
    lcd.print(":");
    lcd.print(minute, DEC);
    lcd.print(":");
    lcd.print(second, DEC);
    lcd.print(" ");
    lcd.print(dayOfMonth, DEC);
    lcd.print("/");
    lcd.print(month, DEC);
    lcd.print("/");
    lcd.print(year,DEC);
    lcd.setCursor(0, 1);
    lcd.print("Cycle: ");
    lcd.print(cycle);
    //Serial.print("Day of week:");
}
void setup() {
    Wire.begin();
    pinMode(greenled, OUTPUT);
    pinMode(redled, OUTPUT);
    pinMode(buttonPin, INPUT);
    lcd.begin(16, 2);
    lcd.setCursor(0, 0);
    lcd.print("XyliboxLabs !");
    timer0 = 0; // clear the timer at the end of startup
    int order = 0;
    // Open serial communications and wait for port to open:
    Serial.begin(9600);
    setDateDs1307(); //Set current time;
    while (!Serial) {
        ; // wait for serial port to connect. Needed for Leonardo only
    }
    if (!bmp.begin()) {
        Serial.println("Could not find a valid BMP085 sensor, check wiring!");
        digitalWrite(greenled, LOW); // green led
        digitalWrite(redled, HIGH); // red led
        while (1) {
        }
    }
    SendShit(); // Everything is fine, let's grab and send
}
void loop()
{
    buttonState = digitalRead(buttonPin);
    // check if the pushbutton is pressed.
    // if it is, the buttonState is HIGH:
    if (buttonState == HIGH) {
        SendShit();
    }
    else {
        // nothing here
    }
    // if there are incoming bytes available
    // from the server, read them and print them:
    if (client.available()) {
        //   char c = client.read();
        //   Serial.print(c);
        // read incoming bytes:
        stringpresent = 0;
        char inChar = client.read();
        // add incoming byte to end of line:
        currentLine += inChar;
        // if you get a newline, clear the line:
        if (inChar == '\n') {
            currentLine = "";
        }
        // if the current line ends with <text>, it will
        // be followed by the answer:
        if ( currentLine.endsWith("<arduino>")) {
            // answer is beginning. Clear the answer string:
            readingAnswer = true;
            answer = "";
        }
        // if you're currently reading the bytes of a answer,
        // add them to the answer String:
        if (readingAnswer) {
            if (inChar != '<') {
                answer += inChar;
            }
            else {
                // if you got a "<" character,
                // you've reached the end of the answer:
                readingAnswer = false;
                stringpresent = 1;
                Serial.println(answer);
                if(answer == ">OK"){
                    digitalWrite(redled, LOW); // Red LED
                    digitalWrite(greenled, HIGH); // Green LED
                    Serial.println("OK");
                }
                else
                if(answer == ">NO"){
                    digitalWrite(redled, LOW);
                    digitalWrite(greenled, HIGH);
                    Serial.println("NO ORDER EVERYTHING IS FINE");
                }
                else
                if(answer == ">YES") {
                    digitalWrite(redled, LOW);
                    digitalWrite(greenled, HIGH);
                    Serial.println("ORDER ASKED !");
                    order = 1;
                }
                else  // Rest of answers are for error handling
                if(answer == ">BAD_KEY") {
                    digitalWrite(greenled, LOW);
                    digitalWrite(redled, HIGH);
                    Serial.println("BAD KEY !");
                }
                else
                if(answer == ">BAD_PARAM") {
                    digitalWrite(greenled, LOW);
                    digitalWrite(redled, HIGH);
                    Serial.println("BAD PARAM !");
                }
                else
                if(answer == ">BAD_FLOAT") {
                    digitalWrite(greenled, LOW);
                    digitalWrite(redled, HIGH);
                    Serial.println("BAD FLOAT !");
                }
                else
                if(answer == ">BAD_CYCLE") {
                    digitalWrite(greenled, LOW);
                    digitalWrite(redled, HIGH);
                    Serial.println("BAD CYCLE !");
                }
                else
                if(answer == ">BAD_ANGLE") {
                    digitalWrite(greenled, LOW);
                    digitalWrite(redled, HIGH);
                    Serial.println("BAD ANGLE !");
                }
                else
                if(answer == ">BAD_HUMIDITY") {
                    digitalWrite(greenled, LOW);
                    digitalWrite(redled, HIGH);
                    Serial.println("BAD HUMIDITY !");
                }
                else
                if(answer != ">BAD_HUMIDITY") { // There something wrong with your panel !
                    digitalWrite(greenled, LOW);
                    digitalWrite(redled, HIGH);
                    Serial.println("UNKNOWN ANSWER FROM SERVER");
                }
                client.stop(); // close the connection to the server:
            }
        }
    }
    // if the server's disconnected, stop the client:
    if (!client.connected()) {
        //Serial.println();
        //Serial.println("disconnecting.");
        client.stop();
        
        if (stringpresent == 0) {
          Serial.println("SERVER ERROR/PATH ERR");
          digitalWrite(greenled, LOW);
          digitalWrite(redled, HIGH);
          stringpresent = 1; // we set the value to '1' to don't flood the serial monitor, user have already a visual to see something went wrong due to the RED LED.
        }
        
        if(order == 1){
            SendShit();
        }
        if (timer0 > interval) {
            // http://www.forward.com.au/pfod/ArduinoProgramming/TimingDelaysInArduino.html
            timer0 -= interval; //reset the timer
            SendShit();
        }
    }
    if (timer1 > interval2) {
        timer1 -= interval2; //reset the timer
        getDateDs1307();//get the time data from tiny RTC
    }
    if (timer2 > interval3) {
        timer2 -= interval3; //reset the timer
        GateOrder();
    }
}
void SendShit()
{
    client.stop();
    delay(2000);
    int i,x,y,z;
    double angle;
    Wire.beginTransmission(HMC5883_WriteAddress);
    Wire.write(regb);
    Wire.write(regbdata);
    Wire.endTransmission();
    delay(1000);
    Wire.beginTransmission(HMC5883_WriteAddress); //Initiate a transmission with HMC5883 (Write address).
    Wire.write(HMC5883_ModeRegisterAddress);       //Place the Mode Register Address in send-buffer.
    Wire.write(HMC5883_ContinuousModeCommand);     //Place the command for Continuous operation Mode in send-buffer.
    Wire.endTransmission();                       //Send the send-buffer to HMC5883 and end the I2C transmission.
    delay(100);
    Wire.beginTransmission(HMC5883_WriteAddress);  //Initiate a transmission with HMC5883 (Write address).
    Wire.requestFrom(HMC5883_WriteAddress,6);      //Request 6 bytes of data from the address specified.
    delay(500);
    //Read the value of magnetic components X,Y and Z
    if(6 <= Wire.available()) // If the number of bytes available for reading be <=6.
    {
        for(i=0;i<6;i++)
        {
            outputData[i]=Wire.read();  //Store the data in outputData buffer
        }
    }
    x=outputData[0] << 8 | outputData[1]; //Combine MSB and LSB of X Data output register
    z=outputData[2] << 8 | outputData[3]; //Combine MSB and LSB of Z Data output register
    y=outputData[4] << 8 | outputData[5]; //Combine MSB and LSB of Y Data output register
    angle= atan2((double)y,(double)x) * (180 / 3.14159265) + 180; // angle in degrees
    /*
    Refer the following application note for heading calculation.
    http://www.ssec.honeywell.com/magnetic/datasheets/lowcost.pdf
    ----------------------------------------------------------------------------------------
    atan2(y, x) is the angle in radians between the positive x-axis of a plane and the point
    given by the coordinates (x, y) on it.
    ----------------------------------------------------------------------------------------
    This sketch does not utilize the magnetic component Z as tilt compensation can not be done without an Accelerometer
  ----------------->y
  |
  |
  |
  |
  |
  |
 \/
  x

     N
 NW  |  NE
     | 
W----------E
     |
 SW  |  SE
     S

    */
    //Print the approximate direction
    Serial.print("You are heading ");
    if((angle < 22.5) || (angle > 337.5 ))
    Serial.print("South");
    if((angle > 22.5) && (angle < 67.5 ))
    Serial.print("South-West");
    if((angle > 67.5) && (angle < 112.5 ))
    Serial.print("West");
    if((angle > 112.5) && (angle < 157.5 ))
    Serial.print("North-West");
    if((angle > 157.5) && (angle < 202.5 ))
    Serial.print("North");
    if((angle > 202.5) && (angle < 247.5 ))
    Serial.print("NorthEast");
    if((angle > 247.5) && (angle < 292.5 ))
    Serial.print("East");
    if((angle > 292.5) && (angle < 337.5 ))
    Serial.print("SouthEast");
    Serial.print(": Angle between X-axis and the South direction ");
    if((0 < angle) && (angle < 180) )
    {
        angle=angle;
    }
    else
    {
        angle=360-angle;
    }
    Serial.print(angle,2);
    Serial.println(" Deg");
    delay(1);
    dht.begin();
    cycle = cycle + 1;
    luminosityValue = analogRead(luminosityPin); // Read the value of the photoresistor
    luminosityFinal = luminosityValue/10;
    float h = dht.readHumidity();
    float t = dht.readTemperature();
    float Pa = bmp.readAltitude(101964);
    float a = bmp.readPressure();
    // check if returns are valid, if they are NaN (not a number) then something went wrong!
    if (isnan(t) || isnan(h)) {
        Serial.println("Failed to read from DHT");
        digitalWrite(greenled, LOW); // green led
        digitalWrite(redled, HIGH); // red led
    }
    else {
        Serial.println("[========= iNFOS ==========]");
        Serial.print("Humidity: ");
        Serial.print(h);
        Serial.print(" %\t");
        Serial.print("Temperature: ");
        Serial.print(t);
        Serial.println(" *C");
        // GY-65
        Serial.print("Altitude: ");
        Serial.print(Pa);
        Serial.print(" M  \t");
        // you can get a more precise measurement of altitude
        // if you know the current sea level pressure which will
        // vary with weather and such. If it is 1015 millibars
        // that is equal to 101500 Pascals.
        Serial.print("Pressure: ");
        Serial.print(a);
        Serial.println(" Pa");
        Serial.print("Luminosity: ");
        Serial.print(luminosityFinal);
        Serial.print(" Lux\t");
        Serial.print("Angle: ");
        Serial.print(angle);
        Serial.println(" Deg");
        Serial.print("Cycle: ");
        Serial.println(cycle);
    }
    // start the Ethernet connection:
    if (Ethernet.begin(mac) == 0) {
        Serial.println("Failed to configure Ethernet using DHCP");
        digitalWrite(greenled, LOW); // green led
        digitalWrite(redled, HIGH); // red led
        // no point in carrying on, so do nothing forevermore:
        // try to congifure using IP address instead of DHCP:
        Ethernet.begin(mac, ip);
    }
    // give the Ethernet shield two second to initialize:
    delay(2000);
    Serial.println("[======== ETHERNET ========]");
    Serial.println("connecting...");
    // if you get a connection, report back via serial:
    if (client.connect(server, 80)) {
        Serial.println("connected");
        order = 0;
        // Make a HTTP request:
        String stringOne = floatToString(buffer, h , 2); // Warning, shit is deprecated ! http://playground.arduino.cc/Main/FloatToString but that the only easy solution i've found, dtostrf(); is pain !
        String stringTwo = floatToString(buffer, t , 2);
        String stringThree = floatToString(buffer, a , 1);
        String stringFour = floatToString(buffer, Pa , 2);
        String stringFive = floatToString(buffer, angle , 2);
        String stringSix = "GET " + stationPath + "?page=gate&api_key=" + arduinoPassword + "&humidity=" + stringOne + "&temperature=" + stringTwo + "&pressure=" + stringThree + "&altitude=" + stringFour + "&luminosity=" + luminosityFinal + "&cycle=" + cycle + "&angle=" + stringFive + " HTTP/1.1";
        Serial.println(stringSix);
        client.println(stringSix);
        client.println("Host: " + host2);
        client.println("Connection: close");
        client.println();
    }
    else {
        // if you didn't get a connection to the server:
        Serial.println("connection failed");
        digitalWrite(greenled, LOW); // green led
        digitalWrite(redled, HIGH); // red led
    }
}
void GateOrder(){
    if (Ethernet.begin(mac) == 0) {
        Serial.println("Failed to configure Ethernet using DHCP");
        digitalWrite(greenled, LOW); // green led
        digitalWrite(redled, HIGH); // red led
        // no point in carrying on, so do nothing forevermore:
        // try to congifure using IP address instead of DHCP:
        Ethernet.begin(mac, ip);
    }
    // give the Ethernet shield two second to initialize:
    delay(1000);
    Serial.println("[======== ORDER ========]");
    Serial.println("connecting...");
    // if you get a connection, report back via serial:
    if (client.connect(server, 80)) {
        Serial.println("connected");
        // Make a HTTP request:
        String stringSix = "GET " + stationPath + "?page=cycle&check&api_key=" + arduinoPassword + " HTTP/1.1";
        Serial.println(stringSix);
        client.println(stringSix);
        client.println("Host: " + host2);
        client.println("Connection: close");
        client.println();
    }
}