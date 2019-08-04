#!/usr/bin/env python
# -*- coding: utf-8 -*-

#  kostal_em_query - Read only query of the Kostal Smart Energy Meter using TCP/IP modbus protocol
#  Copyright (C) 2018  Kilian Knoll 
#  
#  This program is free software: you can redistribute it and/or modify
#  it under the terms of the GNU General Public License as published by
#  the Free Software Foundation, either version 3 of the License, or
#  (at your option) any later version.
#
#  This program is distributed in the hope that it will be useful,
#  but WITHOUT ANY WARRANTY; without even the implied warranty of
#  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
#  GNU General Public License for more details.
#
#  You should have received a copy of the GNU General Public License
#  along with this program.  If not, see <http://www.gnu.org/licenses/>.
#
#
#  Please note that any incorrect or careless usage of this module as well as errors in the implementation can damage your Smart Energy Meter!
#  Therefore, the author does not provide any guarantee or warranty concerning to correctness, functionality or performance and does not accept any liability for damage caused by this module, examples or mentioned information.
#  Thus, use it at your own risk!
#
#
#  Purpose: 
#           Query values from Kostal Smart Energy Meter 
#           
#  Based on the documentation provided by Kostal:
#  https://www.kostal-solar-electric.com/de-de/download/-/media/document-library-folder---kse/2019/05/09/13/57/ba_kostal_interface_ksem---201905.pdf
#
# Requires pymodbus
# Tested with:
#
# Please change the IP address of your Inverter (e.g. 192.168.178.41 and Port (default 1502) to suite your environment - see below)
#

import pymodbus
import sys
import json
from pymodbus.client.sync import ModbusTcpClient
from pymodbus.constants import Endian
from pymodbus.payload import BinaryPayloadDecoder
from pprint import pprint

class kostal_em_query:
    def __init__(self):
        #Change the IP address and port to suite your environment:
        self.inverter_ip=sys.argv[1]
        self.inverter_port=sys.argv[2]
        #No more changes required beyond this point
        self.KostalRegister = []
        self.Adr0=[]
        self.Adr0=[0]
        self.Adr0.append("Active power+")
        self.Adr0.append("U32")
        self.Adr0.append(0)
		
        self.Adr2=[]
        self.Adr2=[2]
        self.Adr2.append("Active power-")
        self.Adr2.append("U32")
        self.Adr2.append(0)		
		
        self.Adr4=[]
        self.Adr4=[4]
        self.Adr4.append("Reactive power+")
        self.Adr4.append("U32")
        self.Adr4.append(0)
		
        self.Adr6=[]
        self.Adr6=[6]
        self.Adr6.append("Reactive power-")
        self.Adr6.append("U32")
        self.Adr6.append(0)		
		
        self.Adr16=[]
        self.Adr16=[16]
        self.Adr16.append("Apparent power+")
        self.Adr16.append("U32")
        self.Adr16.append(0)
		
        self.Adr18=[]
        self.Adr18=[18]
        self.Adr18.append("Apparent power-")
        self.Adr18.append("U32")
        self.Adr18.append(0)
		
        self.Adr24=[]
        self.Adr24=[24]
        self.Adr24.append("Power factor")
        self.Adr24.append("I32")
        self.Adr24.append(0)			

        self.Adr26=[]
        self.Adr26=[26]
        self.Adr26.append("Supply frequency")
        self.Adr26.append("U32")
        self.Adr26.append(0)	

        self.Adr512=[]
        self.Adr512=[512]
        self.Adr512.append("Active energy+")
        self.Adr512.append("UInt64")
        self.Adr512.append(0)	
		
        self.Adr516=[]
        self.Adr516=[516]
        self.Adr516.append("Active energy-")
        self.Adr516.append("UInt64")
        self.Adr516.append(0)	

        self.Adr520=[]
        self.Adr520=[520]
        self.Adr520.append("Reactive energy+")
        self.Adr520.append("UInt64")
        self.Adr520.append(0)	

        self.Adr524=[]
        self.Adr524=[524]
        self.Adr524.append("Reactive energy-")
        self.Adr524.append("UInt64")
        self.Adr524.append(0)			

        self.Adr544=[]
        self.Adr544=[544]
        self.Adr544.append("Apparent energy+")
        self.Adr544.append("UInt64")
        self.Adr544.append(0)	

        self.Adr548=[]
        self.Adr548=[548]
        self.Adr548.append("Apparent energy-")
        self.Adr548.append("UInt64")
        self.Adr548.append(0)	
		
      #  self.Adr60=[]
      #  self.Adr60=[60]
      #  self.Adr60.append("Current L1")
      #  self.Adr60.append("U32")
      #  self.Adr60.append(0)
        
        self.Adr8192=[]
        self.Adr8192 =[8192]
        self.Adr8192.append("ManufacturerID")
        self.Adr8192.append("UInt16")
        self.Adr8192.append(0) 
        

      
    #-----------------------------------------
    # Routine to read a string from one address with 8 registers 
    def ReadStr8(self,myadr_dec):   
        r1=self.client.read_holding_registers(myadr_dec,8,unit=71)
        STRG8Register = BinaryPayloadDecoder.fromRegisters(r1.registers, byteorder=Endian.Big)
        result_STRG8Register =STRG8Register.decode_string(8)      
        return(result_STRG8Register) 
    #-----------------------------------------
    # Routine to read a Float from one address with 2 registers     
    def ReadFloat(self,myadr_dec):
        r1=self.client.read_holding_registers(myadr_dec,2,unit=71)
        FloatRegister = BinaryPayloadDecoder.fromRegisters(r1.registers, byteorder=Endian.Big, wordorder=Endian.Big)
        result_FloatRegister =round(FloatRegister.decode_32bit_float(),2)
        return(result_FloatRegister)   
    #-----------------------------------------
    # Routine to read a U16 from one address with 1 register 
    def ReadU16_1(self,myadr_dec):
        r1=self.client.read_holding_registers(myadr_dec,1,unit=71)
        U16register = BinaryPayloadDecoder.fromRegisters(r1.registers, byteorder=Endian.Big, wordorder=Endian.Big)
        result_U16register = U16register.decode_16bit_uint()
        return(result_U16register)
    #-----------------------------------------
    # Routine to read a Int32 from one address with 1 register 
    def ReadInt32(self,myadr_dec):
        r1=self.client.read_holding_registers(myadr_dec,2,unit=71)
        U32register = BinaryPayloadDecoder.fromRegisters(r1.registers, byteorder=Endian.Big, wordorder=Endian.Big)
        result_U32register = U32register.decode_32bit_int()
        return(result_U32register)
    #-----------------------------------------
	# Routine to read a UInt64 from one address with 1 register 
    def ReadUInt64(self,myadr_dec):
        r1=self.client.read_holding_registers(myadr_dec,4,unit=71)
        U64register = BinaryPayloadDecoder.fromRegisters(r1.registers, byteorder=Endian.Big, wordorder=Endian.Big)
        result_U64register = U64register.decode_64bit_uint()
        return(result_U64register)
    #-----------------------------------------
    # Routine to read a U16 from one address with 2 registers 
    def ReadU16_2(self,myadr_dec):
        r1=self.client.read_holding_registers(myadr_dec,2,unit=71)
        U16register = BinaryPayloadDecoder.fromRegisters(r1.registers, byteorder=Endian.Big, wordorder=Endian.Big)
        result_U16register = U16register.decode_16bit_uint()
        return(result_U16register)
    #-----------------------------------------
    # Routine to read a U32 from one address with 2 registers 
    def ReadU32(self,myadr_dec):
        r1=self.client.read_holding_registers(myadr_dec,2,unit=71)
        #print ("r1 ", rl.registers)
        U32register = BinaryPayloadDecoder.fromRegisters(r1.registers, byteorder=Endian.Big, wordorder=Endian.Big)
        #print ("U32register is", U32register)
        #result_U32register = U32register.decode_32bit_float()
        result_U32register = U32register.decode_32bit_uint()
        return(result_U32register)
    #-----------------------------------------
    def ReadU32new(self,myadr_dec):
        print ("I am in ReadU32new with", myadr_dec)
        r1=self.client.read_holding_registers(myadr_dec,2,unit=71)
        U32register = BinaryPayloadDecoder.fromRegisters(r1.registers, byteorder=Endian.Big, wordorder=Endian.Big)
        result_U32register = U32register.decode_32bit_uint()
        return(result_U32register)
    #-----------------------------------------    
    # Routine to read a U32 from one address with 2 registers 
    def ReadS16(self,myadr_dec):
        r1=self.client.read_holding_registers(myadr_dec,1,unit=71)
        S16register = BinaryPayloadDecoder.fromRegisters(r1.registers, byteorder=Endian.Big, wordorder=Endian.Big)
        result_S16register = S16register.decode_16bit_uint()
        return(result_S16register)
                          
        
    try:
        def run(self):
            
            self.client = ModbusTcpClient(self.inverter_ip,port=self.inverter_port)            
            self.client.connect()

            # LONG List of reads...
            self.Adr0[3]=self.ReadU32(self.Adr0[0])*0.1
            self.Adr2[3]=self.ReadU32(self.Adr2[0])*0.1
            self.Adr4[3]=self.ReadU32(self.Adr4[0])*0.1
            self.Adr6[3]=self.ReadU32(self.Adr6[0])*0.1			
            self.Adr16[3]=self.ReadU32(self.Adr16[0])*0.1
            self.Adr18[3]=self.ReadU32(self.Adr18[0])*0.1				
            self.Adr24[3]=self.ReadInt32(self.Adr24[0])*0.001
            self.Adr26[3]=self.ReadInt32(self.Adr26[0])*0.001			
            #self.Adr60[3]=self.ReadU32(self.Adr60[0])*0.001
            self.Adr512[3]=self.ReadUInt64(self.Adr512[0])*0.1	
            self.Adr516[3]=self.ReadUInt64(self.Adr516[0])*0.1		
            self.Adr520[3]=self.ReadUInt64(self.Adr520[0])*0.1		
            self.Adr524[3]=self.ReadUInt64(self.Adr524[0])*0.1					
            self.Adr544[3]=self.ReadUInt64(self.Adr544[0])*0.1
            self.Adr548[3]=self.ReadUInt64(self.Adr548[0])*0.1
            self.Adr8192[3]=self.ReadU16_1(self.Adr8192[0])
			
            
            self.KostalRegister=[]
            self.KostalRegister.append(self.Adr0)
            self.KostalRegister.append(self.Adr2)
            self.KostalRegister.append(self.Adr4)
            self.KostalRegister.append(self.Adr6)			
            self.KostalRegister.append(self.Adr16)
            self.KostalRegister.append(self.Adr18)				
            self.KostalRegister.append(self.Adr24)					
            self.KostalRegister.append(self.Adr26)	
            #self.KostalRegister.append(self.Adr60)
            self.KostalRegister.append(self.Adr512)	
            self.KostalRegister.append(self.Adr516)		
            self.KostalRegister.append(self.Adr520)		
            self.KostalRegister.append(self.Adr524)	
            self.KostalRegister.append(self.Adr544)
            self.KostalRegister.append(self.Adr548)			
            self.KostalRegister.append(self.Adr8192)
            
            self.client.close()


    except Exception as ex:
            print ("XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX")
            print ("XXX- Hit the following error :From subroutine kostal_em_query :", ex)
            print ("XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX")
#-----------------------------


if __name__ == "__main__":  
    #print ("Starting QUERY .......... ")
    try:
        Kostalvalues =[]
        Kostalquery = kostal_em_query()
        Kostalquery.run()
    except Exception as ex:
        print ("Issues querying Kostal Plenticore -ERROR :", ex)

    KostalVal ={}
    for elements in Kostalquery.KostalRegister:
        KostalVal.update({elements[1]: elements[3]})

    #for elements in Kostalquery.KostalRegister:
    #    print ( elements[1], elements[3])
    print(json.dumps(KostalVal, sort_keys=True, indent=4, separators=(',', ': ')))
    #print ("Done...")
    #pprint(Kostalquery.KostalRegister)
    ##########################################
    #print ("----------------------------------")

    
