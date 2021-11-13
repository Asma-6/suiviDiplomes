import React ,{useState , useEffect} from 'react';
import PropTypes from 'prop-types';
import { makeStyles, createMuiTheme, MuiThemeProvider } from '@material-ui/core/styles';
import AppBar from '@material-ui/core/AppBar';
import Tabs from '@material-ui/core/Tabs';
import Tab from '@material-ui/core/Tab';
import Typography from '@material-ui/core/Typography';
import LinearProgress from '@material-ui/core/LinearProgress';
import Paper from '@material-ui/core/Paper';
import Box from '@material-ui/core/Box';
import Form from "./Form";
import userService from "../../Services/userService";
import Message from "./Message";

function TabPanel(props) {
  const { children, value, index, ...other } = props;

  return (
    <div
      role="tabpanel"
      hidden={value !== index}
      id={`scrollable-auto-tabpanel-${index}`}
      aria-labelledby={`scrollable-auto-tab-${index}`}
      {...other}
    >
      {value === index && (
        <Box p={3}>
          <Typography>{children}</Typography>
        </Box>
      )}
    </div>
  );
}

TabPanel.propTypes = {
  children: PropTypes.node,
  index: PropTypes.any.isRequired,
  value: PropTypes.any.isRequired,
};

function a11yProps(index) {
  return {
    id: `scrollable-auto-tab-${index}`,
    'aria-controls': `scrollable-auto-tabpanel-${index}`,
  };
}

const useStyles = makeStyles((theme) => ({
  root: {
    width: '70%',
    backgroundColor: theme.palette.background.paper,
  },
  container :{
    marginTop: theme.spacing(15),
    display: 'flex',
    justifyContent: 'center',
    alignItems: 'center',
  },
  loading: {
    marginBottom: -theme.spacing(3),
    color: '#0268B5',
  },
}));

const theme = createMuiTheme({
  palette: {
      secondary: {
          main: '#a104fc'
      }
  }
});

export default function FormsPage(props) {
  // States
  const classes = useStyles();
  const [value, setValue] = useState('');
  const [formDeug, setFormDeug] = useState('');
  const [formLicence, setFormLicence] = useState('');
  const [message, setMessage] = useState('');
  const [open, setOpen] = useState(false);
  const [load, setLoad] = useState(false);

  useEffect(() => {
    setLoad(true);
    // list of the two forms (DEUG and Licence)
    userService.getAllForms().then((response) => {
        setLoad(false);
        response.data.forms.forEach(form => {
          if(form.type_formulaire === "DEUG"){
            setFormDeug(form);
          }else if(form.type_formulaire === "Licence"){
            setFormLicence(form);
          }
          setValue(0);
        });
      }).catch((err) => {
        console.log(err);
        setLoad(false);
        setMessage("Erreur de chargement, veuillez réessayer.");
        setOpen(true);
    })
  }, []);

  // change the tab from DEUG to Licence or the opposite
  const handleChange = (event, newValue) => {
    setValue(newValue);
  };

  // open the success or the error message
  const handleCallBackOpen = (open) => {
    setOpen(open);
  };

  return (
    <Paper className={classes.paper}>
     {open && <Message message={message} success="error" callBackOpen={handleCallBackOpen}/>}
      <AppBar position="static" color="default">
        <Tabs
          value={value}
          onChange={handleChange}
          indicatorColor="primary"
          style={{color: '#a104fc'}}
          variant="fullWidth"
          aria-label="scrollable auto tabs example"
        >
          <Tab label="Formulaire de demande de DEUG" {...a11yProps(0)} />
          <Tab label="Formulaire de demande de Licence" {...a11yProps(1)} />
        </Tabs>
        {load && (
          <div className={classes.load}>
          <MuiThemeProvider theme={theme}>
              <LinearProgress color='secondary'/>
          </MuiThemeProvider>
          </div>
        )}
      </AppBar>
      <TabPanel value={value} index={0}>
        <Form form={formDeug} />
      </TabPanel>
      <TabPanel value={value} index={1}>
        <Form form={formLicence} />
      </TabPanel>
</Paper>
  );
}