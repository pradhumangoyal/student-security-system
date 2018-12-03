import cv2
import os
from PIL import Image, ImageDraw, ImageFont
from pytz import timezone
import datetime
import requests
movieCheck = 0
def leave(sid):
    d = datetime.datetime.utcnow().astimezone(timezone('Asia/Kolkata'))
    if d.hour  >= 16:
        requests.post("http://172.31.77.20:5140/post/" + sid)

def save():
    exists = os.path.isfile('movie.mp4')
    if exists:
        os.system("C:\\ffmpeg\\bin\\ffmpeg -r 0.03 -i C:\\Users\\Puaar\\Desktop\\Face-Detection-Recognition-Using-OpenCV-in-Python-master\\projection.png -vcodec mpeg4 -y movie1.mp4")
        os.system("omxplayer -b C:\\Users\\Puaar\\Desktop\\Face-Detection-Recognition-Using-OpenCV-in-Python-master\\movie1.mp4")
        os.remove('movie.mp4')
    else:
        os.system("C:\\ffmpeg\\bin\\ffmpeg -r 0.03 -i C:\\Users\\Puaar\\Desktop\\Face-Detection-Recognition-Using-OpenCV-in-Python-master\\projection.png -vcodec mpeg4 -y movie.mp4")
        os.system("omxplayer -b C:\\Users\\Puaar\\Desktop\\Face-Detection-Recognition-Using-OpenCV-in-Python-master\\movie1.mp4")
        os.remove('movie1.mp4')

def database_text(sid):
    if sid != '':
        sid = "0"
        result = requests.post("http://172.31.77.20:5140/get/" + sid)
        data = result.json()
        username = data[1]
        lateleave = data[5]
        late = data[7]
        gender = ""
        if int(data[6]) is 0:
            gender = "Male"
        else:
            gender = "Female"
        if int(data[7]) is 0:
            late = "No"
        else:
            late = "Yes"

        email = data[2]
        message = "Username: " + username + "\n" + "Lateleave: " + str(lateleave) + "\n" + \
            "Gender: " + str(gender) + "\n" + "Late leave: " + late
        print(message)
        leave(sid)
        image = Image.new("L", (500, 500), "white")
        draw = ImageDraw.Draw(image)
        font = ImageFont.truetype(r'BRITANIC.TTF', size=40)
        (x, y) = (50, 50)
        color = 'rgb(1, 1, 1)'
        draw.multiline_text((x, y), message, fill=color, font=font)
        (x, y) = (60, 60)
        del draw
        image.save('projection.png')
        save()



def draw_boundary(img, classifier, scaleFactor, minNeighbors, color, text, clf):
    # Converting image to gray-scale
    gray_img = cv2.cvtColor(img, cv2.COLOR_BGR2GRAY)
    # detecting features in gray-scale image, returns coordinates, width and height of features
    features = classifier.detectMultiScale(gray_img, scaleFactor, minNeighbors)
    coords = []
    s =0
    # drawing rectangle around the feature and labeling it
    for (x, y, w, h) in features:
        cv2.rectangle(img, (x,y), (x+w, y+h), color, 2)
        # Predicting the id of the user
        id, _ = clf.predict(gray_img[y:y+h, x:x+w])
        # Check for id of user and label the rectangle accordingly

        if(s!= str(id)):
            s = str(id)
            database_text(s)
        cv2.putText(img, s, (x, y-4), cv2.FONT_HERSHEY_SIMPLEX, 0.8, color, 1, cv2.LINE_AA)
        coords = [x, y, w, h]

    return coords

# Method to recognize the person
def recognize(img, clf, faceCascade):
    color = {"blue": (255, 0, 0), "red": (0, 0, 255), "green": (0, 255, 0), "white": (255, 255, 255)}
    coords = draw_boundary(img, faceCascade, 1.1, 10, color["white"], "Face", clf)
    return img


# Loading classifier
faceCascade = cv2.CascadeClassifier('haarcascade_frontalface_default.xml')

# Loading custom classifier to recognize
clf = cv2.face.LBPHFaceRecognizer_create()
clf.read("classifier.xml")

# Capturing real time video stream. 0 for built-in web-cams, 0 or -1 for external web-cams
video_capture = cv2.VideoCapture(0)

while True:
    # Reading image from video stream
    _, img = video_capture.read()
    # Call method we defined above
    img = recognize(img, clf, faceCascade)
    # Writing processed image in a new window
    cv2.imshow("face detection", img)
    if cv2.waitKey(1) & 0xFF == ord('q'):
        break

# releasing web-cam
video_capture.release()
# Destroying output window
cv2.destroyAllWindows()