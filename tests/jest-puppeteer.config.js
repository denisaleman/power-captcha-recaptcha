const config = {
	launch: { 
	  headless: false, 
	  slowMo: 30, 
	  disableFeatures: ['PasswordLeakDetection'],
	  width: 1024, 
	  height: 1024,
	} 
  }

module.exports = config;