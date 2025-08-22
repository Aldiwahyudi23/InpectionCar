import { ref } from 'vue';

export function useImageHandling(processingCanvas) {
  const loadImageWithDimensions = (file) => {
    return new Promise((resolve) => {
      const reader = new FileReader();
      reader.onload = (e) => {
        const img = new Image();
        img.onload = () => {
          resolve({
            file,
            preview: URL.createObjectURL(file),
            rotation: 0,
            width: img.naturalWidth,
            height: img.naturalHeight,
            isNew: true
          });
        };
        img.onerror = () => {
          resolve({ file, preview: URL.createObjectURL(file), rotation: 0, width: 0, height: 0, isNew: true });
        };
        img.src = e.target.result;
      };
      reader.readAsDataURL(file);
    });
  };

  const applyRotationToImage = (imageObject) => {
    return new Promise((resolve) => {
      if (imageObject.rotation === 0 || !processingCanvas.value) {
        if (!imageObject.isNew && !imageObject.file) {
          resolve({ file: null, width: imageObject.width, height: imageObject.height, isOriginal: true });
          return;
        }
        resolve({ file: imageObject.file, width: imageObject.width, height: imageObject.height });
        return;
      }

      const img = new Image();
      img.onload = () => {
        const canvas = processingCanvas.value;
        const context = canvas.getContext('2d');
        const originalWidth = img.width;
        const originalHeight = img.height;

        let newCanvasWidth, newCanvasHeight;
        if (imageObject.rotation === 90 || imageObject.rotation === 270) {
          newCanvasWidth = originalHeight;
          newCanvasHeight = originalWidth;
        } else {
          newCanvasWidth = originalWidth;
          newCanvasHeight = originalHeight;
        }

        canvas.width = newCanvasWidth;
        canvas.height = newCanvasHeight;

        context.clearRect(0, 0, canvas.width, canvas.height);
        context.translate(canvas.width / 2, canvas.height / 2);
        context.rotate(imageObject.rotation * Math.PI / 180);
        context.drawImage(img, -originalWidth / 2, -originalHeight / 2, originalWidth, originalHeight);
        context.setTransform(1, 0, 0, 1, 0, 0);

        canvas.toBlob((blob) => {
          const newFile = new File([blob], imageObject.file ? imageObject.file.name : `rotated_image_${Date.now()}.png`, { type: imageObject.file ? imageObject.file.type : 'image/png' });
          resolve({ file: newFile, width: newCanvasWidth, height: newCanvasHeight });
        }, imageObject.file ? imageObject.file.type : 'image/png');
      };
      img.onerror = () => {
        resolve({ file: imageObject.file, width: imageObject.width, height: imageObject.height });
      };
      img.src = imageObject.preview;
    });
  };

  const getImageSrc = (image) => {
    return image.preview || (image.image_path ? `/${image.image_path}` : '');
  };

  const getImageContainerStyle = (image) => {
    if (!image.width || !image.height || image.width === 0 || image.height === 0) {
      return {
        paddingBottom: '100%',
        position: 'relative'
      };
    }
    const aspectRatioPercentage = (image.height / image.width) * 100;
    return {
      paddingBottom: `${aspectRatioPercentage}%`,
      position: 'relative'
    };
  };

  return {
    loadImageWithDimensions,
    applyRotationToImage,
    getImageSrc,
    getImageContainerStyle
  };
}