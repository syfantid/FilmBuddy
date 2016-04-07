package database;

import java.io.File;
import java.io.FileInputStream;
import java.io.IOException;
import java.sql.Connection;
import java.sql.DriverManager;
import java.sql.SQLException;
import java.util.Properties;

/**
 * Class to represent a Database object
 * Created by Sofia on 4/7/2016.
 */
public class Database {
    private static String myDriver;
    private static String myUrl;
    private static  String user;
    private static String pass;
    private Connection connection;

    /**
     * Initializes the properties based on properties file given in input directory
     */
    static {
        Properties properties = new Properties();
        try {
            properties.load(new FileInputStream(new File("input\\credentials.properties")));
        } catch (IOException e) {
            e.printStackTrace();
        }

        myDriver = properties.getProperty("driver");
        myUrl = properties.getProperty("url");
        user = properties.getProperty("username");
        pass = properties.getProperty("password");
    }

    public boolean connect()  {
        try {
            Class.forName(myDriver);
            connection = DriverManager.getConnection(myUrl, user, pass);
            return true;
        } catch (ClassNotFoundException e) {
            e.printStackTrace();
            return false;
            // Could not find the database driver
        } catch (SQLException e) {
            e.printStackTrace();
            return false;
            // Could not connect to the database
        }
    }

    public Connection getConnection() {
        return connection;
    }
}