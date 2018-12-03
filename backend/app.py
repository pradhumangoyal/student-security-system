import string
from flask import Flask, request, jsonify, send_from_directory, send_file
from flask_sqlalchemy import SQLAlchemy
import json
import pymysql
import datetime
from pytz import timezone
app = Flask(__name__)

@app.route('/get/<userid>', methods=['POST', 'GET'])
def getUser(userid):
    conn = pymysql.connect(host="localhost", user = "root" , password="", db="registration")
    a = conn.cursor()
    sql = "select * from users where sid = " + userid + " " 
    #print(sql +  " == >"  + "sql **********************************" )
    a.execute(sql)
    data = a.fetchone()
    sq = "SELECT * FROM leaverecord where id = "+ userid+"and (l1date = CURDATE() or l2date = CURDATE() or l3date = CURDATE() )"
    numrow = a.execute(sql)
    if numrow > 0:
        data = data + (1,)
    else:
        data = data + (0,)
    return jsonify(data)

@app.route('/post/<userid>', methods=['POST', 'GET'])
def postUser(userid):
    conn = pymysql.connect(host="localhost", user = "root" , password="", db="registration")
    a = conn.cursor()
    sql = "select * from users where sid = " + userid + " " 
    #print(sql +  " == >"  + "sql **********************************" )
    a.execute(sql)
    data = a.fetchone()
    lateleave = int(data[5])
    print(lateleave)
    d = datetime.datetime.utcnow().astimezone(timezone('Asia/Kolkata'))
    if d.hour < 16:
        return jsonify("{ACK: Fail}")
    sq = ""
    if(lateleave == 1) :
        sq = "UPDATE leaverecord set l1late = 1 where id = " + str(data[0])
    elif( lateleave == 2):
        sq = "UPDATE leaverecord set l2late = 1 where id = " + str(data[0])
    else:
        sq = "UPDATE leaverecord set l3late = 1 where id = " + str(data[0])
    print(  " " + sq)
    a.execute(sq)
    conn.commit()
    return jsonify("{ACK: SUCCESS}")


@app.route('/', methods=['POST', 'GET'])
def User():
    return 'Hello'

if __name__ == '__main__' :
    app.run("172.31.77.20",port=5940)
