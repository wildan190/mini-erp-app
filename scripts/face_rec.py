import sys
import json
import face_recognition

def enroll(image_path):
    try:
        image = face_recognition.load_image_file(image_path)
        face_encodings = face_recognition.face_encodings(image)

        if len(face_encodings) == 0:
            print(json.dumps({"success": False, "message": "No face found in the image."}))
            return
        
        if len(face_encodings) > 1:
            print(json.dumps({"success": False, "message": "Multiple faces found. Please provide an image with only one face."}))
            return

        encoding = face_encodings[0].tolist()
        print(json.dumps({
            "success": True, 
            "message": "Face enrolled successfully", 
            "encoding": encoding
        }))
    except Exception as e:
        print(json.dumps({"success": False, "message": str(e)}))

def verify(image_path, known_encoding_json):
    try:
        known_encoding = json.loads(known_encoding_json)
        
        image = face_recognition.load_image_file(image_path)
        face_encodings = face_recognition.face_encodings(image)

        if len(face_encodings) == 0:
            print(json.dumps({"verified": False, "confidence": 0, "message": "No face found in the provided image."}))
            return

        # Assuming we check the first face found in the verification image
        unknown_encoding = face_encodings[0]
        
        # Calculate distance (lower is more similar)
        import numpy as np
        known_enc_np = np.array(known_encoding)
        distance = face_recognition.face_distance([known_enc_np], unknown_encoding)[0]
        
        # A standard threshold is 0.6.
        threshold = 0.6
        verified = bool(distance <= threshold)
        
        # Calculate a pseudo-confidence score (1.0 - distance, bounded between 0 and 1)
        confidence = max(0.0, min(1.0, 1.0 - distance))

        print(json.dumps({
            "verified": verified,
            "confidence": confidence,
            "message": "Face verified successfully" if verified else "Face verification failed"
        }))
    except Exception as e:
         print(json.dumps({
            "verified": False,
            "confidence": 0,
            "message": str(e)
        }))

if __name__ == "__main__":
    if len(sys.argv) < 3:
        print(json.dumps({"success": False, "message": "Insufficient arguments"}))
        sys.exit(1)

    command = sys.argv[1]
    image_path = sys.argv[2]

    if command == "enroll":
        enroll(image_path)
    elif command == "verify":
        if len(sys.argv) < 4:
            print(json.dumps({"verified": False, "confidence": 0, "message": "Known encoding not provided"}))
            sys.exit(1)
        known_encoding_json = sys.argv[3]
        verify(image_path, known_encoding_json)
    else:
        print(json.dumps({"success": False, "message": "Invalid command"}))
        sys.exit(1)
