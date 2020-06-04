import face_recognition
import cv2
import numpy as np
import sys

video_capture = cv2.VideoCapture(sys.argv[1])

user_image = face_recognition.load_image_file(sys.argv[2])
user_face_encoding = face_recognition.face_encodings(user_image)[0]

known_face_encodings = [
    user_face_encoding
]

known_face_names = [
    "user",
]
face_locations = []
face_encodings = []
face_names = []
process_this_frame = True
faceCounter = 0
frames = 0

while video_capture.isOpened():
    ret, frame = video_capture.read()
    if ret != True:
        break
    small_frame = cv2.resize(frame, (0, 0), fx=0.25, fy=0.25)

    rgb_small_frame = small_frame[:, :, ::-1]

    if process_this_frame:

        face_locations = face_recognition.face_locations(rgb_small_frame)
        face_encodings = face_recognition.face_encodings(rgb_small_frame, face_locations)

        face_names = []
        for face_encoding in face_encodings:
            frames += 1
            matches = face_recognition.compare_faces(known_face_encodings, face_encoding)
            name = "Unknown"

            face_distances = face_recognition.face_distance(known_face_encodings, face_encoding)
            best_match_index = np.argmin(face_distances)
            if matches[best_match_index]:
                name = known_face_names[best_match_index]
                faceCounter += 1
            face_names.append(name)

    process_this_frame = not process_this_frame
    if cv2.waitKey(1) & 0xFF == ord('q'):
        break


print(str(frames) + ',' + str(faceCounter))
