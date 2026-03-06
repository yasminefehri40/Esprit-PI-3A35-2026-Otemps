import cv2
import sys
import os
import uuid

faces_dir = "public/faces"

# 1️⃣ Lire le chemin de l'image
input_file = sys.argv[1]

img = cv2.imread(input_file)

face_cascade = cv2.CascadeClassifier(cv2.data.haarcascades +
                                      "haarcascade_frontalface_default.xml")

gray = cv2.cvtColor(img, cv2.COLOR_BGR2GRAY)

faces = face_cascade.detectMultiScale(gray, 1.3, 5)

if len(faces) == 0:
    print("NO_FACE")
    sys.exit()

(x, y, w, h) = faces[0]
face = img[y:y+h, x:x+w]

if not os.path.exists(faces_dir):
    os.makedirs(faces_dir)

filename = "face_" + str(uuid.uuid4()) + ".png"
filepath = os.path.join(faces_dir, filename)

cv2.imwrite(filepath, face)

print(filename)