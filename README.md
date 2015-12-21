LaCrosse
========

Lacrosse Weather Station Server (phpLaxbro). this is expermental code to enable
registering a Lacrosse Technologies GW-1000U gateway and a Lacrosse Technologies
C84612 wether station.  Because of the great work on this software by
karlc@keckec.com and the information provided by Skydiver, this software will
now decode the weather data from the C84612 weather station.  I've just added
using this data to upload to weather underground, see below for configuration.

My weather station using this software is here
http://www.wunderground.com/personal-weather-station/dashboard?ID=KCAPETAL29

You should read this thread on the wxfourm about this and skydivers windows
software: http://www.wxforum.net/index.php?topic=14299.75

The idea behind this code as oppsed to skydivers great software, it it could be
adapted to a home router using DDWRT or hosted on a cheap host that supports
PHP.  Much of the data decoding derived from skydiver's investigation and from
karlc work on this file. 

All doucmentation is here in this single file.

One word of warning, you should register your gateway and weather station to the
lacrosse servers first if you ever wish to use them in the future.  Once they
have been initally registered you should be able to use this software, skydivers
software or the lacrosse alerts service without problems and switch at any time
between them.

This software emulates the Lacrosse Alerts api on their server with the URI
location of /request.breq.  You must setup your server to resolve .breq files as
PHP files to use this extention as a PHP file. This can be done by adding a line
into your .htaccess file:

    AddHandler php5-script .php .breq

Once this is setup, you will also need to set the gateway to use your server.
The simplest way it to set your server as the "proxy" using the Gateway Advance
Setup (GAS) utility available from Lacrosse.

Requests all goto the URI /request.breq

Each request has a special HTTP request header called HTTP_IDENTIFY, which
specifies the the request type, identification of the gateway, and the gateway
KEY.


    HTTP_IDENTIFY: 8009A427:01:1ACD476DFAC43232:01
                   ^^  ^^   ^^        ^^        ^^
                   A   B    C         D         E

This is from my (KEC) capture

    HTTP_IDENTIFY: 80097B29:00:914C428A9FD46E58:70

Each packet types seems to consist of

A=80, always 80 so far (2 chars).
B= MAC address less vendor ID (6 chars)
C= Packet Code 1 (2 chars)
D=RegistrationCode or Device Serial Number or other Identifier (16 chars)
E= Packet Code (2 chars)

From here on in, each packet type will be called by C:E or as in the above
example packet (01:01)

Each request may or may not have post data.

Each reply will also have a corresponding HTTP_FLAGS header, it will only be the
2 return codes like so:

    HTTP_FLAGS : 00:00

Each request has a corresponding return code placed in the HTTP_FLAGS header,
and the response may or maynot have data.

Packets
-------
00:01 - Gateway Power up Packet (when gateway is unregistered), reply with empty message (HTTP_FLAGS: 10:00)

00:10 -

00:20 - Gateway Unregistered Push Button Packet, you can respond to this message with a gateway config response (HTTP_FLAGS: 20:00)

00:30 - Gateway finished registering packet, respond with (HTTP_FLAGS: 30:00)

00:70 - Gateway Ping Packet (nothing to do with weather station, just keeps the gateway happy)

01:00 - Weather Station Ping.  Sets time, lights internet light on station  -- Reply with header(HTTP_FLAGS: 14:01)

01:01 - Weather Station Data packet, this contains the weather station data.

01:14 - Weather Station Registrion verification packet

7F:10 - Weather Station Registration Packet

00:14 - 


Data Packet layout
------------------
The data is sent from the gateway as post data in a 01:01 record.  The records
are 197 bytes, although users have reported other sized packets too. That will
need investigation The layout of the 197-byte record is as follows. Start and
end byte numbers end with H or L to indicate which nybble the field starts or
ends with. H or L indicate the high-order or low-order nybble. The length is
given in nybbles, not bytes.

___________________________________
    |Strt|Len in|
Strt|nyb |nybble|Encoding |Function
___________________________________

00H   0    2      byte     Record type, always 01
01H   2    4      ???      Unknown
03H   6    3      byte     status?
04L   9    10     BDC      Date/Time of Max Inside Temp
09L   13   10     BCD      Date/Time of Min Inside Temp
0eL   1d   3      BCD      Max Inside Temp
10H   20   2      ???      Unknown
11H   22   3      BCD      Min Inside Temp
12L   25   2      ???      Unknown
13L   27   3      BCD      Current Inside Temp
15H   2a   3      ???      Unknown
16L   2d   10     BCD      Date/Time of Max Outside Temp
1bL   37   10     BCD      Date/Time of Min Outside Temp
20L   41   3      BCD      Max Outside Temp
22H   44   2      ???      Unknown
23H   46   3      BCD      Min Outside Temp
24L   49   2      ???      Unknown
25L   4b   3      BCD      Current Outside Temp
27H   4e   3      ???      Unknown
28L   51   10     BCD      Unknown Date/Time 1
2dL   5b   10     BCD      Unknown Date/Time 2
32L   65   10     ???      Unknown
37L   6f   3      BCD      Second copy of outside temp(?)
39H   72   2      ???      Status byte—per skydvr 0xA0—error
3aH   74   10     BCD      Date/Time of Max Inside Humidity
3fH   7e   10     BCD      Date/Time of Min Inside Humidity
44H   88   2      binary   Max Inside Humidity
45H   8a   2      binary   Min Inside Humidity
46H   8c   2      binary   Current Inside Humidity
47H   8e   10     BCD      Date/Time of Max Outside Humidity
4cH   98   10     BCD      Date/Time of Min Outside Humidity
51H   a2   2      binary   Max Outside Humidity
52H   a4   2      binary   Min Outside Humidity
53H   a6   2      binary   Current Outside Humidity
54H   a8   18     ???      Unknown all 0s
5dH   ba   4      ???      Unknown
5fH   be   20     ???      Unknown all 0s
69H   d2   2      ???      Unknown
6aH   d4   10     BCD      Unknown Date/Time 3
6fH   de   12     ???      Unknown
75H   ea   10     BCD      Date/Time last 1-hour rain window ended
7aH   f4   13     ???      Unknown
80L   101  10     BCD      Date/Time of Last Rain Reset
85L   10b  23     ???      Unknown—skydvr says rainfall array
91H   122  4      binary   Current Ave Wind Speed
93H   126  4      ???      Unknown
95H   12a  6      nybbles  Wind direction history -- One nybble per time period
98H   130  10     BCD      Time of Max Wind Gust
9dH   13a  4      binary   Max Wind Gust since reset in 100th of km/h
9fH   13e  2      ???      Unknown
a0H   140  4      binary   Max Wind Gust this Cycle in 100th of km/h
a2H   144  4      ???      Unknown—skydvr says wind status
a4H   148  6      nybbles  Second copy of wind direction history?
a7H   14e  1      ???      Unknown
a7L   14f  4      BCD      Current barometer in inches Hg
a9L   153  6      ???      Unknown—skydvr says 0xAA might be pressure delta
acL   159  4      BCD      Min Barometer
aeL   15d  6      ???      Unknown
b1L   163  4      BCD      Max Barometer
b3L   167  5      ???      Unknown
b6H   16c  10     BCD      Unknown Date/Time 5
bbH   176  10     BCD      Unknown Date/Time 6
c0H   180  6      ???      Unknown
c3H   186  2      binary   Checksum1
c4H   188  2      binary   Checksum2 May be one 16-bit checksum


Data Field Notes:
1. Date/Time fields are BCD spanning 10 nybbles. It starts with the 2-digit 
   year (without century), then 2-digit month, then 2-digit day, then 2-digit 
   hour, then 2-digit minute.
2. Barometer is BCD in hundredths of an inch of mercury.
3. Wind speed and gust are in hundredths of km/h
4. Humidity is one byte, in percent
5. Temperatures are in hundredths of a degree C, plus 40 degrees C



Registration Gateway
--------------------
Registration takes place in 2 parts, gateway registration and weather station
registration.   Gateway registration is straight forwared and should pose no
issues to reregister as many times as you like.   You can reset a gateway to
default by pressing and holding the button while powering up the gateway.  It
can then be re-registered.

After Registration the Gateway will ping the service with a 00:70 every so
often, this frequency is set in the reply to this packet.  Typically 240
seconds.

Registration Weather Station
----------------------------
Two types of registration a new registration and a re-registration.   A new
registration will set a serial number in the weather station, so I recommend you
do the first registraton with Lacrosse alerts to get a "good" serial number
written.  After that you can re-register using this software, and if you ever
want to go back you can.

Pressing the Rain button on the weather station should have it blink "REG", then
push the gateway button.  A 7F:10 - Weather Station Registration Packet should
be generated.  This will include what the weather station thinks its serial
number is, if starts with 7FFF then it has been registered before, and you must
reply with this address.  If 0102030405060708 then it has not been registered
before, and what ever serial number you reply with will be written to the
weather station.

Once this packet has been received by the weather station a 01:14 packet will be
sent with the new serial number (or old one if reregistered), and once this is
replied to the weather station is registered.

The weather station will then send  01:01 data packets and 01:00 ping packets to
the service.  The ping packets must be responded to correctly to keep the
"internet" indicator up and to keep the weather station "registered" over the
long term.

Other Info
----------
Pressing the RAIN button until beep on a registered weather station fluses out
data packets from the weather station.

To Investigate --------------- I think on the lacrosse alerts website you can
change the rate of data packets, we should change this setting and see what
happens in these packets.   I thought at one point I could change this, but I've
lost that data.

Weather Underground Upload --------------------------- Below you can set $wug_pw
to your weather underground password and $wug_sid to your weather station ID,
and when a 197 bytes data packet is received it will upload it to weather
underground.

Setting $wu_send_indoor_values to non zero below will send indoor values to WU,
at this time I see no reason to do this.

To Investigate
---------------
I think on the lacrosse alerts website you can change the rate of data packets,
we should change this setting and see what happens in these packets.   I thought
at one point I could change this, but I've lost that data.

Lastly
------
Pressing the RAIN button until beep on a registered weather station fluses out data packets from the weather station.
