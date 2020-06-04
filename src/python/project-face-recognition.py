import face_recognition
import cv2
import sys

face1 = face_recognition.load_image_file(sys.argv[1])
face1Encoding = face_recognition.face_encodings(face1)[0]

face2 = face_recognition.load_image_file(sys.argv[2])
faceEncodings = face_recognition.face_encodings(face2)

check = face_recognition.compare_faces(face1Encoding, faceEncodings)
print(check[0])